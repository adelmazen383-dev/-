<?php

namespace App\Http\Controllers;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\ContractTemplate;
use App\Models\Invoice;
use App\Http\Requests\StoreContractRequest;
use App\Services\ContractService;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function __construct(
        private ContractService $contractService
    ) {}

    public function index(Request $request)
    {
        $query = Contract::with('customer')->latest();

        // Fix #11: Group OR conditions to prevent breaking AND filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%")
                          ->orWhere('national_id', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contracts = $query->paginate(15);
        return view('contracts.index', compact('contracts'));
    }

    public function create(Request $request)
    {
        $lessors = Customer::where('type', 'lessor')->orderBy('name')->get();
        $lessees = Customer::where('type', 'lessee')->orderBy('name')->get();
        $templates = ContractTemplate::where('is_active', true)->get();

        $prefill = [];
        $invoice = null;

        if ($request->filled('invoice_id')) {
            $invoice = Invoice::findOrFail((int) $request->invoice_id);

            // Find or create customer (lessee) from invoice details
            $lesseeCustomer = null;
            if (!empty($invoice->national_id)) {
                $lesseeCustomer = Customer::where('national_id', $invoice->national_id)->first();
            }
            if (!$lesseeCustomer && !empty($invoice->phone)) {
                $lesseeCustomer = Customer::where('phone', $invoice->phone)->first();
            }
            if (!$lesseeCustomer) {
                $lesseeCustomer = Customer::create([
                    'name'        => $invoice->customer_name,
                    'national_id' => $invoice->national_id ?: ('10' . str_pad((string)mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT)),
                    'phone'       => $invoice->phone,
                    'type'        => 'lessee',
                ]);
            }

            // Ensure the newly found or created customer is in $lessees collection
            if (!$lessees->contains('id', $lesseeCustomer->id)) {
                $lessees->push($lesseeCustomer);
            }

            $propertyParts = [];
            if ($invoice->property_name) {
                $propertyParts[] = 'منشأة / عقار: ' . $invoice->property_name;
            }
            if ($invoice->suite_number || $invoice->apartment_number) {
                $propertyParts[] = 'وحدة / جناح رقم ' . ($invoice->suite_number ?: $invoice->apartment_number);
            }
            if ($invoice->property_location) {
                $propertyParts[] = 'الموقع: ' . $invoice->property_location;
            }

            $prefill = [
                'customer_id'      => $lesseeCustomer->id,
                'rent_amount'      => (float) ($invoice->agreed_amount ?: $invoice->amount),
                'deposit_amount'   => (float) ($invoice->deposit_amount ?? 0),
                'start_date'       => $invoice->check_in_date ? $invoice->check_in_date->format('Y-m-d\TH:i') : null,
                'end_date'         => $invoice->check_out_date ? $invoice->check_out_date->format('Y-m-d\TH:i') : null,
                'property_details' => count($propertyParts) > 0 ? implode(' — ', $propertyParts) : ('وحدة سكنية رقم ' . ($invoice->suite_number ?: $invoice->apartment_number)),
                'additional_terms' => $invoice->notes,
            ];
        }

        return view('contracts.create', compact('lessors', 'lessees', 'templates', 'prefill', 'invoice'));
    }

    /**
     * Fix #8: Removed dead WhatsApp Job dispatch.
     * Fix #10: Business logic delegated to ContractService.
     */
    public function store(StoreContractRequest $request)
    {
        $validated = $request->validated();
        $validated['template_id'] = 1; // Default to apartment template

        $contract = $this->contractService->createContract(
            $validated,
            auth()->id()
        );

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'تم إنشاء العقد بنجاح. يمكنك الآن إرسال رابط التوقيع للمستأجر.');
    }

    public function show(Contract $contract)
    {
        $contract->load('customer', 'lessor', 'logs', 'signatures', 'creator');
        return view('contracts.show', compact('contract'));
    }

    /**
     * Mark contract as sent — called when admin clicks send/copy link.
     * Only transitions draft → sent.
     */
    public function send(Contract $contract)
    {
        $this->contractService->markAsSent($contract);
        return back()->with('success', 'تم تسجيل إرسال العقد بنجاح.');
    }

    public function cancel(Contract $contract)
    {
        $this->authorize('cancel', $contract);
        try {
            $this->contractService->cancelContract($contract);
            return back()->with('success', 'تم إلغاء العقد بنجاح.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show edit form — only for contracts that haven't been signed yet.
     */
    public function edit(Contract $contract)
    {
        if ($contract->isTerminal()) {
            return back()->with('error', 'لا يمكن تعديل عقد في حالته الحالية.');
        }

        $lessors = Customer::where('type', 'lessor')->orderBy('name')->get();
        $lessees = Customer::where('type', 'lessee')->orderBy('name')->get();
        $templates = ContractTemplate::where('is_active', true)->get();
        return view('contracts.edit', compact('contract', 'lessors', 'lessees', 'templates'));
    }

    /**
     * Update contract — only for non-terminal contracts.
     * Regenerates draft PDF with new data.
     */
    public function update(StoreContractRequest $request, Contract $contract)
    {
        if ($contract->isTerminal()) {
            return back()->with('error', 'لا يمكن تعديل عقد في حالته الحالية.');
        }

        $validated = $request->validated();
        $validated['template_id'] = 1;

        $contract->update($validated);
        $contract->load('customer', 'template');

        // Regenerate draft PDF with updated data
        $generator = app(\App\Services\ContractGeneratorService::class);
        $pdfPath = $generator->generateDraftPdf($contract);
        $contract->update(['pdf_path' => $pdfPath]);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'تم تحديث العقد وإعادة توليد المسودة بنجاح.');
    }

    /**
     * Regenerate PDF for an existing contract (useful when status/dates change)
     */
    public function regeneratePdf(Contract $contract, \App\Services\ContractGeneratorService $generator)
    {
        if ($contract->status === \App\Enums\ContractStatus::SIGNED) {
            $contract->update(['signed_pdf_path' => $generator->generateSignedPdf($contract)]);
        } else {
            $contract->update(['pdf_path' => $generator->generateDraftPdf($contract)]);
        }
        return back()->with('success', 'تم تحديث ملف الـ PDF بنجاح ليعكس الحالة الحالية.');
    }

    /**
     * Delete contract (Soft Delete).
     */
    public function destroy(Contract $contract)
    {
        $contract->delete();
        return redirect()->route('contracts.index')->with('success', 'تم حذف العقد بنجاح.');
    }
}
