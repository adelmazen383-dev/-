<?php

namespace App\Http\Controllers;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\ContractTemplate;
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

    public function create()
    {
        $lessors = Customer::where('type', 'lessor')->orderBy('name')->get();
        $lessees = Customer::where('type', 'lessee')->orderBy('name')->get();
        $templates = ContractTemplate::where('is_active', true)->get();
        return view('contracts.create', compact('lessors', 'lessees', 'templates'));
    }

    /**
     * Fix #8: Removed dead WhatsApp Job dispatch.
     * Fix #10: Business logic delegated to ContractService.
     */
    public function store(StoreContractRequest $request)
    {
        $contract = $this->contractService->createContract(
            $request->validated(),
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

        $contract->update($request->validated());
        $contract->load('customer', 'template');

        // Regenerate draft PDF with updated data
        $generator = app(\App\Services\ContractGeneratorService::class);
        $pdfPath = $generator->generateDraftPdf($contract);
        $contract->update(['pdf_path' => $pdfPath]);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'تم تحديث العقد وإعادة توليد المسودة بنجاح.');
    }
}
