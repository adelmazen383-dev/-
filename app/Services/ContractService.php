<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContractService
{
    public function __construct(
        private ContractGeneratorService $generator
    ) {}

    /**
     * Create a new contract with draft PDF generation.
     *
     * @param array $validatedData Validated form data
     * @param int   $userId        The admin user creating the contract
     */
    public function createContract(array $validatedData, int $userId): Contract
    {
        return DB::transaction(function () use ($validatedData, $userId) {
            $contractNumber = $this->generator->generateUniqueNumber();

            $contract = Contract::create(array_merge($validatedData, [
                'contract_number' => $contractNumber,
                'created_by'      => $userId,
            ]));

            // Eager load relations needed for PDF generation
            $contract->load('customer', 'template');

            $pdfPath = $this->generator->generateDraftPdf($contract);
            $contract->update(['pdf_path' => $pdfPath]);

            Log::info('Contract created', [
                'contract_id'     => $contract->id,
                'contract_number' => $contractNumber,
                'created_by'      => $userId,
            ]);

            return $contract;
        });
    }

    /**
     * Process digital signature submission from customer.
     *
     * Fix #2: Entire flow wrapped in DB::transaction.
     * Fix #6: Base64 signature validated before processing.
     */
    public function processSignature(Contract $contract, string $signatureData, Request $request): Contract
    {
        if ($contract->isTerminal()) {
            throw new \DomainException('هذا العقد غير متاح للتوقيع.');
        }

        // Fix #6: Validate base64 data structure and image type
        $this->validateSignatureData($signatureData);

        return DB::transaction(function () use ($contract, $signatureData, $request) {
            // Decode and save signature image
            $signaturePath = $this->saveSignatureImage($contract, $signatureData);

            // Create signature record with forensic data
            $contract->signature()->create([
                'signature_path' => $signaturePath,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'signed_at'      => now(),
            ]);

            // Update contract status
            $contract->update([
                'status'    => ContractStatus::SIGNED,
                'signed_at' => now(),
            ]);

            // Reload the signature relation for PDF generation
            $contract->load('signature', 'customer', 'template');

            // Generate signed PDF with embedded signature + QR
            $signedPdfPath = $this->generator->generateSignedPdf($contract, $signaturePath);
            $contract->update(['signed_pdf_path' => $signedPdfPath]);

            Log::info('Contract signed', [
                'contract_id' => $contract->id,
                'ip_address'  => $request->ip(),
                'user_agent'  => Str::limit($request->userAgent(), 100),
            ]);

            return $contract;
        });
    }

    /**
     * Reject a contract by the customer.
     */
    public function rejectContract(Contract $contract): Contract
    {
        if ($contract->isTerminal()) {
            throw new \DomainException('لا يمكن تعديل حالة هذا العقد.');
        }

        $contract->update([
            'status'      => ContractStatus::REJECTED,
            'rejected_at' => now(),
        ]);

        Log::info('Contract rejected', ['contract_id' => $contract->id]);

        return $contract;
    }

    /**
     * Cancel a contract by admin.
     */
    public function cancelContract(Contract $contract): Contract
    {
        if (in_array($contract->status, [ContractStatus::SIGNED, ContractStatus::CANCELLED])) {
            throw new \DomainException('لا يمكن إلغاء هذا العقد في حالته الحالية.');
        }

        $contract->update(['status' => ContractStatus::CANCELLED]);

        Log::info('Contract cancelled', [
            'contract_id' => $contract->id,
            'cancelled_by' => auth()->id(),
        ]);

        return $contract;
    }

    /**
     * Mark contract as viewed when customer opens sign page.
     */
    public function markAsViewed(Contract $contract): void
    {
        if ($contract->status === ContractStatus::SENT) {
            $contract->update([
                'status'    => ContractStatus::VIEWED,
                'viewed_at' => now(),
            ]);
        }
    }

    // ─── Private Helpers ────────────────────────────────────

    /**
     * Validate the base64 signature data.
     *
     * @throws \InvalidArgumentException
     */
    private function validateSignatureData(string $signatureData): void
    {
        $maxSizeKb = config('rental.signature.max_size_kb', 512);
        $allowedTypes = config('rental.signature.allowed_types', ['png']);

        // Check base64 structure
        if (!preg_match('/^data:image\/(' . implode('|', $allowedTypes) . ');base64,/', $signatureData, $matches)) {
            throw new \InvalidArgumentException('نوع ملف التوقيع غير مسموح به. الأنواع المسموحة: ' . implode(', ', $allowedTypes));
        }

        // Check decoded size
        $base64Part = explode(';base64,', $signatureData)[1] ?? '';
        $decodedSize = (int) (strlen($base64Part) * 3 / 4);

        if ($decodedSize > $maxSizeKb * 1024) {
            throw new \InvalidArgumentException("حجم التوقيع يتجاوز الحد المسموح ({$maxSizeKb}KB).");
        }
    }

    /**
     * Save the base64-encoded signature image to storage.
     */
    private function saveSignatureImage(Contract $contract, string $signatureData): string
    {
        $parts = explode(';base64,', $signatureData);
        $imageBase64 = base64_decode($parts[1]);

        $fileName = 'signatures/' . $contract->contract_number . '_' . Str::random(10) . '.png';
        Storage::disk('public')->put($fileName, $imageBase64);

        return 'storage/' . $fileName;
    }
}
