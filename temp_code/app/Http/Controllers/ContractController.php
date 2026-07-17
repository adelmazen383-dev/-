<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\ContractTemplate;
use App\Http\Requests\StoreContractRequest;
use App\Services\ContractGeneratorService;
use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::with('customer')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('contract_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%");
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
        $customers = Customer::all();
        $templates = ContractTemplate::where('is_active', true)->get();
        return view('contracts.create', compact('customers', 'templates'));
    }

    public function store(StoreContractRequest $request, ContractGeneratorService $generator)
    {
        $contractNumber = $generator->generateUniqueNumber();
        
        $contract = Contract::create(array_merge(
            $request->validated(),
            ['contract_number' => $contractNumber]
        ));

        // Generate Draft PDF
        $pdfPath = $generator->generateDraftPdf($contract);
        $contract->update(['pdf_path' => $pdfPath]);

        // Dispatch WhatsApp Job
        SendWhatsAppMessageJob::dispatch($contract);

        return redirect()->route('contracts.index')->with('success', 'تم إنشاء العقد وإرساله للعميل بنجاح.');
    }

    public function show(Contract $contract)
    {
        $contract->load('customer', 'logs');
        return view('contracts.show', compact('contract'));
    }

    public function cancel(Contract $contract)
    {
        if (in_array($contract->status, ['signed', 'cancelled'])) {
            return back()->with('error', 'لا يمكن إلغاء هذا العقد في حالته الحالية.');
        }

        $contract->update(['status' => 'cancelled']);
        return back()->with('success', 'تم إلغاء العقد بنجاح.');
    }

    public function resendWhatsapp(Contract $contract)
    {
        if (in_array($contract->status, ['cancelled', 'signed', 'rejected'])) {
            return back()->with('error', 'لا يمكن إرسال رابط هذا العقد.');
        }

        SendWhatsAppMessageJob::dispatch($contract);
        return back()->with('success', 'تم جدولة إعادة إرسال الرابط للعميل.');
    }
}
