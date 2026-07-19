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
     * Determines who should sign based on contract status.
     */
    public function show($token)
    {
        $contract = Contract::where('verification_token', $token)
            ->with('customer', 'lessor')
            ->firstOrFail();

        // If contract is already fully signed/rejected/cancelled, redirect to verification
        if ($contract->isTerminal()) {
            return redirect('/verify?contract_number=' . $contract->contract_number);
        }

        // Log that the page was viewed
        $this->contractService->markAsViewed($contract);

        // Determine whose turn it is to sign
        $role = $contract->status === ContractStatus::SIGNED_BY_LESSEE ? 'lessor' : 'lessee';

        return view('contracts.sign', compact('contract', 'role'));
    }

    /**
     * Process the signature submission (public route).
     *
     * - Lessee signs first → status becomes signed_by_lessee
     * - Lessor signs second → status becomes signed + final PDF generated
     */
    public function store(Request $request, $token)
    {
        $contract = Contract::where('verification_token', $token)->firstOrFail();

        $request->validate([
            'signature' => 'required|string|max:1048576', // Max ~750KB base64
        ]);

        try {
            $wasSignedByLessee = $contract->status !== ContractStatus::SIGNED_BY_LESSEE;

            $contract = $this->contractService->processSignature(
                $contract,
                $request->signature,
                $request
            );

            // Refresh to get latest status
            $contract->refresh();

            if ($contract->status === ContractStatus::SIGNED) {
                // Lessor just signed → contract is complete, show download
                return response()->json([
                    'message'      => 'تم توقيع العقد بنجاح — العقد مكتمل ✅',
                    'download_url' => asset($contract->signed_pdf_path),
                    'is_complete'  => true,
                ]);
            } else {
                // Lessee just signed → waiting for lessor
                return response()->json([
                    'message'     => 'تم حفظ توقيعك بنجاح. سيتم إرسال العقد للمالك للتوقيع عليه.',
                    'is_complete' => false,
                ]);
            }
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
