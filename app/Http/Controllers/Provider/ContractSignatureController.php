<?php

namespace App\Http\Controllers\Provider;

use App\Enums\ContractStatus;
use App\Http\Controllers\Controller;
use App\Models\ProviderContract;
use App\Services\ProviderContractService;
use Illuminate\Http\Request;

class ContractSignatureController extends Controller
{
    public function __construct(
        private ProviderContractService $contractService
    ) {}

    public function show(string $token)
    {
        $contract = ProviderContract::where('verification_token', $token)
            ->with('provider')
            ->firstOrFail();

        if ($contract->isTerminal()) {
            return redirect('/verify?contract_number=' . urlencode($contract->contract_number));
        }

        $this->contractService->markAsViewed($contract);

        $role = $contract->status === ContractStatus::SIGNED_BY_LESSEE ? 'lessor' : 'lessee';

        return view('provider.contracts.sign', compact('contract', 'role'));
    }

    public function store(Request $request, string $token)
    {
        $contract = ProviderContract::where('verification_token', $token)->firstOrFail();

        $request->validate([
            'signature' => ['required', 'string', 'max:1048576'],
        ]);

        try {
            $contract = $this->contractService->processSignature(
                $contract,
                (string) $request->input('signature'),
                $request
            );

            $contract->refresh();

            if ($contract->status === ContractStatus::SIGNED) {
                return response()->json([
                    'message'      => 'تم توقيع العقد من الطرفين بنجاح — العقد مكتمل وموثّق.',
                    'download_url' => asset($contract->signed_pdf_path),
                    'is_complete'  => true,
                ]);
            }

            return response()->json([
                'message'     => 'تم حفظ توقيعك بنجاح. سيتم إرسال العقد للمالك لإتمام التوقيع النهائي.',
                'is_complete' => false,
            ]);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, string $token)
    {
        $contract = ProviderContract::where('verification_token', $token)->firstOrFail();

        try {
            $this->contractService->rejectContract($contract);
            return response()->json(['message' => 'تم رفض العقد بنجاح.']);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
