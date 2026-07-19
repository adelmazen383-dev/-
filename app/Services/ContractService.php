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

            // Log the creation event
            $contract->logs()->create([
                'event' => 'تم إنشاء العقد كمسودة',
                'meta'  => ['user_id' => $userId],
            ]);

            Log::info('Contract created', [
                'contract_id'     => $contract->id,
                'contract_number' => $contractNumber,
                'created_by'      => $userId,
            ]);

            return $contract;
        });
    }

    /**
     * Mark a contract as "sent" — admin clicked the WhatsApp/copy link button.
     * Transitions: draft → sent
     */
    public function markAsSent(Contract $contract): void
    {
        if (!in_array($contract->status, [ContractStatus::DRAFT, ContractStatus::VIEWED])) {
            return; // Already sent or beyond
        }

        $contract->update([
            'status'  => ContractStatus::SENT,
            'sent_at' => now(),
        ]);

        $contract->logs()->create([
            'event' => 'تم إرسال رابط التوقيع للمستأجر',
            'meta'  => ['user_id' => auth()->id()],
        ]);

        Log::info('Contract marked as sent', ['contract_id' => $contract->id]);
    }

    /**
     * Mark contract as viewed when the person opens the sign page.
     * Works for both lessee (sent→viewed) and lessor (sent_to_lessor is implicit).
     */
    public function markAsViewed(Contract $contract): void
    {
        if ($contract->status === ContractStatus::SENT) {
            $contract->update([
                'status'    => ContractStatus::VIEWED,
                'viewed_at' => now(),
            ]);

            $contract->logs()->create([
                'event' => 'تم فتح رابط التوقيع من قبل المستأجر',
            ]);
        }

        // If lessor is viewing (status is signed_by_lessee), just log it
        if ($contract->status === ContractStatus::SIGNED_BY_LESSEE) {
            $contract->logs()->create([
                'event' => 'تم فتح رابط التوقيع من قبل المالك',
            ]);
        }
    }

    /**
     * Process digital signature submission.
     *
     * Flow:
     *   - If lessee is signing: status → signed_by_lessee (no final PDF yet)
     *   - If lessor is signing: status → signed, generate final PDF
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

            if ($contract->status === ContractStatus::SIGNED_BY_LESSEE) {
                // ═══ LESSOR IS SIGNING (Step 2) ═══
                $contract->signatures()->create([
                    'role'           => 'lessor',
                    'signature_path' => $signaturePath,
                    'ip_address'     => $request->ip(),
                    'user_agent'     => $request->userAgent(),
                    'signed_at'      => now(),
                ]);

                // Update contract status to final SIGNED
                $contract->update([
                    'status'      => ContractStatus::SIGNED,
                    'signed_at'   => now(),
                    'site_profit' => 40.00,
                ]);

                // Reload the signature relations for PDF generation
                $contract->load('signatures', 'customer', 'lessor', 'template');

                // Generate signed PDF with embedded signatures + QR
                $signedPdfPath = $this->generator->generateSignedPdf($contract);
                $contract->update(['signed_pdf_path' => $signedPdfPath]);

                $contract->logs()->create([
                    'event' => 'تم توقيع العقد من المالك — العقد مكتمل ✅',
                    'meta'  => ['ip' => $request->ip()],
                ]);

                Log::info('Contract signed by lessor (finalized)', [
                    'contract_id' => $contract->id,
                    'ip_address'  => $request->ip(),
                ]);
            } else {
                // ═══ LESSEE IS SIGNING (Step 1) ═══
                $contract->signatures()->create([
                    'role'           => 'lessee',
                    'signature_path' => $signaturePath,
                    'ip_address'     => $request->ip(),
                    'user_agent'     => $request->userAgent(),
                    'signed_at'      => now(),
                ]);

                // Update contract status to SIGNED_BY_LESSEE
                $contract->update([
                    'status' => ContractStatus::SIGNED_BY_LESSEE,
                ]);

                $contract->logs()->create([
                    'event' => 'تم توقيع العقد من المستأجر — بانتظار توقيع المالك',
                    'meta'  => ['ip' => $request->ip()],
                ]);

                Log::info('Contract signed by lessee', [
                    'contract_id' => $contract->id,
                    'ip_address'  => $request->ip(),
                ]);
            }

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

        $contract->logs()->create([
            'event' => 'تم رفض العقد',
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

        $contract->logs()->create([
            'event' => 'تم إلغاء العقد من قبل الإدارة',
            'meta'  => ['user_id' => auth()->id()],
        ]);

        Log::info('Contract cancelled', [
            'contract_id' => $contract->id,
            'cancelled_by' => auth()->id(),
        ]);

        return $contract;
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
