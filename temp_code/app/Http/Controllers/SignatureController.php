<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\ContractGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignatureController extends Controller
{
    public function show($token)
    {
        $contract = Contract::where('verification_token', $token)->firstOrFail();

        if (in_array($contract->status, ['signed', 'cancelled', 'rejected'])) {
            return redirect('/verify?contract_number=' . $contract->contract_number . '&status=' . $contract->status);
        }

        if ($contract->status == 'sent') {
            $contract->update([
                'status' => 'viewed',
                'viewed_at' => now()
            ]);
        }

        return view('contracts.sign', compact('contract'));
    }

    public function store(Request $request, $token, ContractGeneratorService $generator)
    {
        $contract = Contract::where('verification_token', $token)->firstOrFail();

        if (in_array($contract->status, ['signed', 'cancelled', 'rejected'])) {
            return response()->json(['message' => 'هذا العقد غير متاح للتوقيع.'], 403);
        }

        $request->validate([
            'signature' => 'required|string'
        ]);

        // Save base64 signature as image
        $imageParts = explode(";base64,", $request->signature);
        if (count($imageParts) < 2) {
            return response()->json(['message' => 'بيانات التوقيع غير صالحة'], 422);
        }

        $imageTypeAux = explode("image/", $imageParts[0]);
        $imageType = $imageTypeAux[1];
        $imageBase64 = base64_decode($imageParts[1]);

        $signatureFileName = 'signatures/' . $contract->contract_number . '_' . Str::random(10) . '.' . $imageType;
        Storage::disk('public')->put($signatureFileName, $imageBase64);
        $signaturePath = 'storage/' . $signatureFileName;

        // Save signature record
        $contract->signature()->create([
            'signature_path' => $signaturePath,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'signed_at' => now()
        ]);

        // Update contract status
        $contract->update([
            'status' => 'signed',
            'signed_at' => now()
        ]);

        // Generate Signed PDF
        $signedPdfPath = $generator->generateSignedPdf($contract, $signaturePath);
        $contract->update(['signed_pdf_path' => $signedPdfPath]);

        return response()->json(['message' => 'تم حفظ التوقيع بنجاح']);
    }

    public function reject(Request $request, $token)
    {
        $contract = Contract::where('verification_token', $token)->firstOrFail();

        if (in_array($contract->status, ['signed', 'cancelled', 'rejected'])) {
            return response()->json(['message' => 'لا يمكن تعديل حالة هذا العقد.'], 403);
        }

        $contract->update([
            'status' => 'rejected',
            'rejected_at' => now()
        ]);

        return response()->json(['message' => 'تم رفض العقد بنجاح']);
    }
}
