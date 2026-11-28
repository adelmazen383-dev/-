<?php

namespace App\Http\Controllers\Provider;

use App\Enums\ContractStatus;
use App\Http\Controllers\Controller;
use App\Models\ProviderContract;
use App\Models\ProviderInvoice;
use App\Models\ServiceProvider;
use App\Services\ProviderContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    public function __construct(
        private ProviderContractService $contractService
    ) {}

    public function index(Request $request)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();

        $query = $provider->contracts()->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                    ->orWhere('lessee_name', 'like', "%{$search}%")
                    ->orWhere('lessee_national_id', 'like', "%{$search}%")
                    ->orWhere('lessee_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereIn('status', ['draft', 'sent', 'viewed', 'signed_by_lessee']);
            } else {
                $query->where('status', $request->status);
            }
        }

        $contracts = $query->paginate(15)->withQueryString();

        return view('provider.contracts.index', compact('provider', 'contracts'));
    }

    public function create(Request $request)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();

        $recentTenants = $provider->contracts()
            ->select('lessee_name', 'lessee_national_id', 'lessee_phone')
            ->latest()
            ->take(40)
            ->get()
            ->unique('lessee_national_id')
            ->values();

        $prefill = [
            'lessor_name'        => $provider->name,
            'lessor_national_id' => $provider->national_id_number ?: '',
            'lessor_phone'       => $provider->phone,
        ];
        $invoice = null;

        if ($request->filled('invoice_id')) {
            $invoice = $provider->invoices()->findOrFail((int) $request->invoice_id);
            $details = is_array($invoice->service_details) ? $invoice->service_details : [];

            $propertyParts = [];
            if ($provider->business_name) {
                $propertyParts[] = $provider->business_name;
            }
            if (! empty($details['floor_number'])) {
                $propertyParts[] = 'الدور: ' . $details['floor_number'];
            }
            if (! empty($details['suite_number'])) {
                $propertyParts[] = 'وحدة / شقة: ' . $details['suite_number'];
            }

            $checkIn = ! empty($details['check_in_date']) ? $details['check_in_date'] . 'T15:00' : null;
            $checkOut = ! empty($details['check_out_date']) ? $details['check_out_date'] . 'T12:00' : null;

            $prefill = array_merge($prefill, [
                'provider_invoice_id' => $invoice->id,
                'lessee_name'         => $invoice->customer_name,
                'lessee_national_id'  => $invoice->customer_national_id ?: '',
                'lessee_phone'        => $invoice->customer_phone,
                'rent_amount'         => (float) ($invoice->agreed_amount ?: $invoice->amount),
                'deposit_amount'      => (float) ($invoice->deposit_amount ?? 0),
                'start_date'          => $checkIn,
                'end_date'            => $checkOut,
                'property_details'    => count($propertyParts) > 0
                    ? implode(' — ', $propertyParts)
                    : ($invoice->title ?: 'وحدة سكنية فندقية'),
                'additional_terms'    => $invoice->notes,
            ]);
        }

        return view('provider.contracts.create', compact('provider', 'recentTenants', 'prefill', 'invoice'));
    }

    public function store(Request $request)
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();

        $validated = $this->validateContract($request);

        if (! empty($validated['provider_invoice_id'])) {
            $ownedInvoice = $provider->invoices()->find((int) $validated['provider_invoice_id']);
            $validated['provider_invoice_id'] = $ownedInvoice?->id;
        }

        $contract = $this->contractService->createContract($provider, $validated);

        return redirect()
            ->route('provider.contracts.show', $contract)
            ->with('success', 'تم إنشاء العقد الإلكتروني بنجاح. يمكنك الآن إرسال رابط التوقيع للمستأجر.');
    }

    public function show(ProviderContract $contract)
    {
        $provider = $this->authorizeOwnership($contract);
        $contract->load('signatures', 'logs', 'invoice');

        return view('provider.contracts.show', compact('provider', 'contract'));
    }

    public function edit(ProviderContract $contract)
    {
        $provider = $this->authorizeOwnership($contract);

        if ($contract->isTerminal() || $contract->status === ContractStatus::SIGNED_BY_LESSEE) {
            return redirect()
                ->route('provider.contracts.show', $contract)
                ->with('error', 'لا يمكن تعديل العقد في حالته الحالية.');
        }

        return view('provider.contracts.edit', compact('provider', 'contract'));
    }

    public function update(Request $request, ProviderContract $contract)
    {
        $this->authorizeOwnership($contract);

        if ($contract->isTerminal() || $contract->status === ContractStatus::SIGNED_BY_LESSEE) {
            return redirect()
                ->route('provider.contracts.show', $contract)
                ->with('error', 'لا يمكن تعديل العقد في حالته الحالية.');
        }

        $validated = $this->validateContract($request);
        $this->contractService->updateContract($contract, $validated);

        return redirect()
            ->route('provider.contracts.show', $contract)
            ->with('success', 'تم تحديث العقد وإعادة توليد المسودة بنجاح.');
    }

    public function send(ProviderContract $contract)
    {
        $this->authorizeOwnership($contract);
        $this->contractService->markAsSent($contract);

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'تم تسجيل إرسال رابط التوقيع بنجاح.');
    }

    public function cancel(ProviderContract $contract)
    {
        $this->authorizeOwnership($contract);

        try {
            $this->contractService->cancelContract($contract);
            return back()->with('success', 'تم إلغاء العقد بنجاح.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function regeneratePdf(ProviderContract $contract)
    {
        $this->authorizeOwnership($contract);
        $this->contractService->regeneratePdf($contract);

        return back()->with('success', 'تم تحديث ملف الـ PDF بنجاح ليعكس الحالة الحالية.');
    }

    public function destroy(ProviderContract $contract)
    {
        $this->authorizeOwnership($contract);
        $contract->delete();

        return redirect()
            ->route('provider.contracts.index')
            ->with('success', 'تم حذف العقد بنجاح.');
    }

    private function authorizeOwnership(ProviderContract $contract): ServiceProvider
    {
        /** @var ServiceProvider $provider */
        $provider = Auth::guard('provider')->user();
        if ((int) $contract->service_provider_id !== (int) $provider->id) {
            abort(403, 'غير مصرح لك بالوصول إلى هذا العقد.');
        }

        return $provider;
    }

    private function validateContract(Request $request): array
    {
        return $request->validate([
            'provider_invoice_id'    => ['nullable', 'integer'],
            'lessor_name'            => ['required', 'string', 'max:150'],
            'lessor_national_id'     => ['required', 'string', 'max:40'],
            'lessor_phone'           => ['required', 'string', 'max:30'],
            'lessee_name'            => ['required', 'string', 'max:150'],
            'lessee_national_id'     => ['required', 'string', 'max:40'],
            'lessee_phone'           => ['required', 'string', 'max:30'],
            'property_details'       => ['required', 'string', 'max:2000'],
            'start_date'             => ['required', 'date'],
            'end_date'               => ['required', 'date', 'after:start_date'],
            'rent_amount'            => ['required', 'numeric', 'min:1', 'max:99999999.99'],
            'deposit_amount'         => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'tourism_license_number' => ['nullable', 'string', 'max:120'],
            'additional_terms'       => ['nullable', 'string', 'max:5000'],
        ], [
            'lessor_name.required'        => 'يرجى إدخال اسم المالك (الطرف الأول).',
            'lessor_national_id.required' => 'يرجى إدخال رقم هوية المالك.',
            'lessor_phone.required'       => 'يرجى إدخال رقم جوال المالك.',
            'lessee_name.required'        => 'يرجى إدخال اسم المستأجر (الطرف الثاني).',
            'lessee_national_id.required' => 'يرجى إدخال رقم هوية المستأجر.',
            'lessee_phone.required'       => 'يرجى إدخال رقم جوال المستأجر.',
            'property_details.required'   => 'يرجى إدخال وصف العقار / رقم الشقة.',
            'start_date.required'         => 'يرجى تحديد تاريخ ووقت بداية العقد.',
            'end_date.required'           => 'يرجى تحديد تاريخ ووقت نهاية العقد.',
            'end_date.after'              => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.',
            'rent_amount.required'        => 'يرجى إدخال قيمة الإيجار.',
            'rent_amount.min'             => 'قيمة الإيجار يجب أن تكون أكبر من صفر.',
            'deposit_amount.required'     => 'يرجى إدخال مبلغ التأمين (أو 0).',
        ]);
    }
}
