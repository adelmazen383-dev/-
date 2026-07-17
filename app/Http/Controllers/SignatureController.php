<?php

namespace App\Http\Controllers;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Services\ContractService;
use Illuminate\Http\Request;

class SignatureController extends Controller
{
    public function __construct(
        private ContractService $contractService
    ) {}

    /**
     * Show the signature page to the customer (public route).
     */
    public function show($token)
    {
        $contract = Contract::where('verification_token', $token)
            ->with('customer')
            ->firstOrFail();

        if ($contract->isTerminal()) {
            return redirect('/verify?contract_number=' . $contract->contract_number);
        }

        $this->contractService->markAsViewed($contract);

        return view('contracts.sign', compact('contract'));
    }

    /**
     * Process the signature submission (public route).
     *
     * Fix #2: Transaction handled in ContractService.
     * Fix #6: Signature validation handled in ContractService.
     */
    public function store(Request $request, $token)
    {
        $contract = Contract::where('verification_token', $token)->firstOrFail();

        $request->validate([
            'signature' => 'required|string|max:1048576', // Max ~750KB base64
        ]);

        try {
            $contract = $this->contractService->processSignature(
                $contract,
                $request->signature,
                $request
            );

            return response()->json([
                'message'      => 'تم حفظ التوقيع بنجاح',
                'download_url' => asset($contract->signed_pdf_path),
            ]);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Reject the contract (public route).
     */
    public function reject(Request $request, $token)
    {
        $contract = Contract::where('verification_token', $token)->firstOrFail();

        try {
            $this->contractService->rejectContract($contract);
            return response()->json(['message' => 'تم رفض العقد بنجاح']);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
