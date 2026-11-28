<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Models\ContractTemplate;
use App\Models\ProviderContract;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProviderContractService
{
    public function generateUniqueNumber(): string
    {
        return DB::transaction(function () {
            $year = date('Y');
            $prefix = 'PRENT';

            $latest = ProviderContract::withTrashed()
                ->lockForUpdate()
                ->where('contract_number', 'like', "{$prefix}-{$year}-%")
                ->orderByDesc('id')
                ->first();

            $nextId = 1;
            if ($latest && preg_match('/(\d{6})$/', $latest->contract_number, $m)) {
                $nextId = ((int) $m[1]) + 1;
            }

            return sprintf('%s-%s-%06d', $prefix, $year, $nextId);
        });
    }

    public function createContract(ServiceProvider $provider, array $data): ProviderContract
    {
        return DB::transaction(function () use ($provider, $data) {
            $contractNumber = $this->generateUniqueNumber();
            $template = ContractTemplate::where('is_active', true)->orderBy('id')->first();

            $contract = ProviderContract::create(array_merge($data, [
                'service_provider_id' => $provider->id,
                'template_id'         => $template?->id,
                'contract_number'     => $contractNumber,
                'status'              => ContractStatus::DRAFT,
            ]));

            $contract->load('template', 'lesseeSignature', 'lessorSignature');
            $pdfPath = $this->generateDraftPdf($contract);
            $contract->update(['pdf_path' => $pdfPath]);

            $contract->logs()->create([
                'event' => 'تم إنشاء العقد كمسودة',
                'meta'  => ['service_provider_id' => $provider->id],
            ]);

            return $contract;
        });
    }

    public function updateContract(ProviderContract $contract, array $data): ProviderContract
    {
        if ($contract->isTerminal() || $contract->status === ContractStatus::SIGNED_BY_LESSEE) {
            throw new \DomainException('لا يمكن تعديل العقد في حالته الحالية.');
        }

        $contract->update($data);
        $contract->load('template', 'lesseeSignature', 'lessorSignature');

        $pdfPath = $this->generateDraftPdf($contract);
        $contract->update(['pdf_path' => $pdfPath]);

        $contract->logs()->create([
            'event' => 'تم تحديث بيانات العقد وإعادة توليد المسودة',
        ]);

        return $contract;
    }

    public function regeneratePdf(ProviderContract $contract): ProviderContract
    {
        $contract->load('template', 'lesseeSignature', 'lessorSignature');

        if ($contract->status === ContractStatus::SIGNED) {
            $contract->update(['signed_pdf_path' => $this->generateSignedPdf($contract)]);
        } else {
            $contract->update(['pdf_path' => $this->generateDraftPdf($contract)]);
        }

        $contract->logs()->create([
            'event' => 'تم تحديث ملف العقد (PDF)',
        ]);

        return $contract;
    }

    public function markAsSent(ProviderContract $contract): void
    {
        if (! in_array($contract->status, [ContractStatus::DRAFT, ContractStatus::VIEWED], true)) {
            return;
        }

        $contract->update([
            'status'  => ContractStatus::SENT,
            'sent_at' => now(),
        ]);

        $contract->logs()->create([
            'event' => 'تم إرسال رابط التوقيع للمستأجر',
        ]);
    }

    public function markAsViewed(ProviderContract $contract): void
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

        if ($contract->status === ContractStatus::SIGNED_BY_LESSEE) {
            $contract->logs()->create([
                'event' => 'تم فتح رابط التوقيع من قبل المالك',
            ]);
        }
    }

    public function processSignature(ProviderContract $contract, string $signatureData, Request $request): ProviderContract
    {
        if ($contract->isTerminal()) {
            throw new \DomainException('هذا العقد غير متاح للتوقيع.');
        }

        $this->validateSignatureData($signatureData);

        return DB::transaction(function () use ($contract, $signatureData, $request) {
            $signaturePath = $this->saveSignatureImage($contract, $signatureData);

            if ($contract->status === ContractStatus::SIGNED_BY_LESSEE) {
                // Step 2: Lessor signs -> contract becomes SIGNED & final PDF generated
                $contract->signatures()->create([
                    'role'           => 'lessor',
                    'signature_path' => $signaturePath,
                    'ip_address'     => $request->ip(),
                    'user_agent'     => $request->userAgent(),
                    'signed_at'      => now(),
                ]);

                $contract->update([
                    'status'    => ContractStatus::SIGNED,
                    'signed_at' => now(),
                ]);

                $contract->load('signatures', 'lesseeSignature', 'lessorSignature', 'template');

                $signedPdfPath = $this->generateSignedPdf($contract);
                $contract->update(['signed_pdf_path' => $signedPdfPath]);

                $contract->logs()->create([
                    'event' => 'تم توقيع العقد من المالك — العقد مكتمل وموثّق',
                    'meta'  => ['ip' => $request->ip()],
                ]);
            } else {
                // Step 1: Lessee signs -> status becomes SIGNED_BY_LESSEE
                $contract->signatures()->create([
                    'role'           => 'lessee',
                    'signature_path' => $signaturePath,
                    'ip_address'     => $request->ip(),
                    'user_agent'     => $request->userAgent(),
                    'signed_at'      => now(),
                ]);

                $contract->update([
                    'status' => ContractStatus::SIGNED_BY_LESSEE,
                ]);

                $contract->logs()->create([
                    'event' => 'تم توقيع العقد من المستأجر — بانتظار توقيع المالك',
                    'meta'  => ['ip' => $request->ip()],
                ]);
            }

            return $contract;
        });
    }

    public function rejectContract(ProviderContract $contract): ProviderContract
    {
        if ($contract->isTerminal()) {
            throw new \DomainException('لا يمكن تعديل حالة هذا العقد.');
        }

        $contract->update([
            'status'      => ContractStatus::REJECTED,
            'rejected_at' => now(),
        ]);

        $contract->logs()->create([
            'event' => 'تم رفض العقد من الطرف المعني',
        ]);

        return $contract;
    }

    public function cancelContract(ProviderContract $contract): ProviderContract
    {
        if (in_array($contract->status, [ContractStatus::SIGNED, ContractStatus::CANCELLED], true)) {
            throw new \DomainException('لا يمكن إلغاء هذا العقد في حالته الحالية.');
        }

        $contract->update(['status' => ContractStatus::CANCELLED]);

        $contract->logs()->create([
            'event' => 'تم إلغاء العقد من قبل مقدم الخدمة',
        ]);

        return $contract;
    }

    /* ── PDF Generation (100% Parity with Core ContractGeneratorService) ── */

    public function generateDraftPdf(ProviderContract $contract): string
    {
        $html = $this->prepareHtml($contract) . $this->getQrHtml($contract);
        $fullHtml = $this->wrapWithHtml($html);

        $mpdf = $this->createMpdf();
        $mpdf->WriteHTML($fullHtml);

        $fileName = 'provider-contracts/drafts/' . $contract->contract_number . '_' . Str::random(5) . '.pdf';
        $fullPath = storage_path('app/public/' . $fileName);

        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $mpdf->Output($fullPath, \Mpdf\Output\Destination::FILE);

        return 'storage/' . $fileName;
    }

    public function generateSignedPdf(ProviderContract $contract): string
    {
        $html = $this->prepareHtml($contract) . $this->getQrHtml($contract);
        $fullHtml = $this->wrapWithHtml($html);

        $mpdf = $this->createMpdf();
        $mpdf->WriteHTML($fullHtml);

        $fileName = 'provider-contracts/signed/' . $contract->contract_number . '_signed_' . Str::random(5) . '.pdf';
        $fullPath = storage_path('app/public/' . $fileName);

        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $mpdf->Output($fullPath, \Mpdf\Output\Destination::FILE);

        return 'storage/' . $fileName;
    }

    private function createMpdf(): Mpdf
    {
        ini_set('max_execution_time', '120');
        ini_set('pcre.backtrack_limit', '10000000');

        $tempDir = storage_path('framework/cache/mpdf-temp');
        if (! file_exists($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $mpdf = new Mpdf([
            'mode'             => 'utf-8',
            'format'           => 'A4',
            'direction'        => 'rtl',
            'default_font'     => 'xbriyaz',
            'tempDir'          => $tempDir,
            'margin_top'       => 15,
            'margin_bottom'    => 15,
            'margin_left'      => 15,
            'margin_right'     => 15,
            'autoArabic'       => true,
            'autoScriptToLang' => true,
            'autoLangToFont'   => true,
        ]);

        $mpdf->simpleTables = true;
        $mpdf->packTableData = true;

        return $mpdf;
    }

    private function prepareHtml(ProviderContract $contract): string
    {
        $html = $contract->template?->html_content
            ?? ContractTemplate::where('is_active', true)->orderBy('id')->value('html_content')
            ?? $this->defaultTemplateHtml();

        if ($contract->end_date && now()->greaterThan($contract->end_date)) {
            $statusBox = '<div style="background-color: #fef2f2; color: #dc2626; border: 2px solid #dc2626; padding: 10px; text-align: center; font-size: 20px; font-weight: bold; margin-bottom: 20px; border-radius: 8px;">حالة العقد: منتهي</div>';
        } else {
            $statusBox = '<div style="background-color: #f0fdf4; color: #16a34a; border: 2px solid #16a34a; padding: 10px; text-align: center; font-size: 20px; font-weight: bold; margin-bottom: 20px; border-radius: 8px;">حالة العقد: فعال</div>';
        }
        $html = $statusBox . $html;

        $logoPath = public_path('images/image.png');
        $logoHtml = file_exists($logoPath)
            ? '<img src="' . $logoPath . '" style="max-height: 140px;" />'
            : '<div style="font-size:24px; font-weight:bold; color:#14532d;">منصة ثقة</div>';

        $additionalTerms = $contract->additional_terms ?: 'لا توجد شروط إضافية';
        $additionalTerms = preg_replace('/([^\s]{70})/u', '$1 ', $additionalTerms);

        $lessorHtml = '<div style="border-bottom: 1px solid #14532d; width: 80%; margin: 40px auto 10px auto;"></div>';
        $lesseeHtml = '<div style="border-bottom: 1px solid #14532d; width: 80%; margin: 40px auto 10px auto;"></div>';

        if ($contract->lessorSignature) {
            $rel = str_replace('storage/', '', $contract->lessorSignature->signature_path);
            if (Storage::disk('public')->exists($rel)) {
                $sigData = base64_encode(Storage::disk('public')->get($rel));
                $signedAt = $contract->lessorSignature->signed_at?->format('Y-m-d H:i:s') ?? '';
                $lessorHtml = '<img src="data:image/png;base64,' . $sigData . '" style="max-height:80px;" />'
                    . '<div style="font-size:10px; color:#6b7280; margin-top:5px;">IP: ' . e($contract->lessorSignature->ip_address) . '<br>الوقت: ' . $signedAt . '</div>';
            }
        }

        if ($contract->lesseeSignature) {
            $rel = str_replace('storage/', '', $contract->lesseeSignature->signature_path);
            if (Storage::disk('public')->exists($rel)) {
                $sigData = base64_encode(Storage::disk('public')->get($rel));
                $signedAt = $contract->lesseeSignature->signed_at?->format('Y-m-d H:i:s') ?? '';
                $lesseeHtml = '<img src="data:image/png;base64,' . $sigData . '" style="max-height:80px;" />'
                    . '<div style="font-size:10px; color:#6b7280; margin-top:5px;">IP: ' . e($contract->lesseeSignature->ip_address) . '<br>الوقت: ' . $signedAt . '</div>';
            }
        }

        return str_replace(
            [
                '{{CONTRACT_NUMBER}}', '{{DATE}}', '{{LOGO_HTML}}',
                '{{CUSTOMER_NAME}}', '{{NATIONAL_ID}}', '{{PROPERTY_DETAILS}}',
                '{{RENT_AMOUNT}}', '{{START_DATE}}', '{{END_DATE}}',
                '{{TOURISM_LICENSE}}', '{{ADDITIONAL_TERMS}}',
                '{{LESSOR_SIGNATURE}}', '{{LESSEE_SIGNATURE}}',
                '{{LESSOR_NAME}}', '{{LESSOR_ID}}', '{{DEPOSIT_AMOUNT}}',
            ],
            [
                e($contract->contract_number),
                now()->format('Y-m-d'),
                $logoHtml,
                e($contract->lessee_name),
                e($contract->lessee_national_id),
                e($contract->property_details),
                number_format((float) $contract->rent_amount, 2),
                $contract->start_date->format('Y-m-d H:i'),
                $contract->end_date->format('Y-m-d H:i'),
                e($contract->tourism_license_number ?: 'لا يوجد'),
                nl2br(e($additionalTerms)),
                $lessorHtml,
                $lesseeHtml,
                e($contract->lessor_name),
                e($contract->lessor_national_id),
                number_format((float) ($contract->deposit_amount ?? 0), 2),
            ],
            $html
        );
    }

    private function getQrHtml(ProviderContract $contract): string
    {
        $qrUrl = url('/verify?contract_number=' . $contract->contract_number);
        $qrCode = base64_encode(QrCode::format('svg')->size(120)->generate($qrUrl));

        return '<div style="text-align:center; margin-top:20px;">'
            . '<img src="data:image/svg+xml;base64,' . $qrCode . '" />'
            . '</div>';
    }

    private function wrapWithHtml(string $body): string
    {
        return '<html dir="rtl"><head>'
            . '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'
            . '<style>'
            . '@media print { * { -webkit-print-color-adjust: exact !important; color-adjust: exact !important; print-color-adjust: exact !important; } }'
            . 'body { font-family: xbriyaz, sans-serif; line-height: 1.8; text-align: right; direction: rtl; font-size: 14px; color: #1e293b; }'
            . 'h1 { font-size: 26px; color: #064e3b; margin: 0 0 10px 0; font-weight: bold; }'
            . 'h3 { font-size: 15px; color: #d4af37; background-color: #064e3b; padding: 8px 15px; margin: 20px 0 10px 0; font-weight: bold; border-radius: 4px; }'
            . 'hr { border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0; }'
            . '</style></head><body>' . $body . '</body></html>';
    }

    private function validateSignatureData(string $signatureData): void
    {
        $maxSizeKb = (int) config('rental.signature.max_size_kb', 512);
        $allowedTypes = config('rental.signature.allowed_types', ['png']);

        if (! preg_match('/^data:image\/(' . implode('|', $allowedTypes) . ');base64,/', $signatureData)) {
            throw new \InvalidArgumentException('نوع ملف التوقيع غير مسموح به. الأنواع المسموحة: ' . implode(', ', $allowedTypes));
        }

        $base64Part = explode(';base64,', $signatureData)[1] ?? '';
        $decodedSize = (int) (strlen($base64Part) * 3 / 4);

        if ($decodedSize > $maxSizeKb * 1024) {
            throw new \InvalidArgumentException("حجم التوقيع يتجاوز الحد المسموح ({$maxSizeKb}KB).");
        }
    }

    private function saveSignatureImage(ProviderContract $contract, string $signatureData): string
    {
        $parts = explode(';base64,', $signatureData);
        $imageBase64 = base64_decode($parts[1]);

        $fileName = 'provider-contracts/signatures/' . $contract->contract_number . '_' . Str::random(10) . '.png';
        Storage::disk('public')->put($fileName, $imageBase64);

        return 'storage/' . $fileName;
    }

    private function defaultTemplateHtml(): string
    {
        return '<div style="background-color: #14532d; color: #ffffff; padding: 10px; text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 20px;">
    رقم ترخيص السياحة للمؤجر : {{TOURISM_LICENSE}}
</div>
<table style="width: 100%; margin-bottom: 20px; table-layout: fixed;">
    <tr>
        <td style="width: 33%; vertical-align: middle; padding-left: 10px;">
            <div style="background-color: #16a34a; color: #ffffff; padding: 8px 12px; border-radius: 4px; font-weight: bold; font-size: 13px; text-align: center;">
                رقم الشقة : {{PROPERTY_DETAILS}}
            </div>
        </td>
        <td style="width: 34%; text-align: center; vertical-align: middle;">{{LOGO_HTML}}</td>
        <td style="width: 33%; vertical-align: middle; padding-right: 10px;">
            <div style="background-color: #16a34a; color: #ffffff; padding: 8px 12px; border-radius: 4px; font-weight: bold; font-size: 13px; text-align: center;">
                رقم العقد : {{CONTRACT_NUMBER}}
            </div>
        </td>
    </tr>
</table>
<div style="border-top: 3px solid #16a34a; margin-bottom: 20px;"></div>
<div style="font-size: 14px; line-height: 1.8; color: #1f2937; margin-bottom: 30px; text-align: justify;">
    تم عمل هذا العقد في يوم <strong>{{DATE}}</strong> بين كل من الطرف الأول (المؤجر) <strong>{{LESSOR_NAME}}</strong> (هوية: <strong>{{LESSOR_ID}}</strong>)،
    والطرف الثاني (المستأجر) <strong>{{CUSTOMER_NAME}}</strong> (هوية: <strong>{{NATIONAL_ID}}</strong>).
</div>
<table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
    <tr><td style="padding: 10px 0;"><div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الأول</div><div style="font-size: 14px; text-align: right; line-height: 1.8;">تأجير الشقة المذكورة أعلاه لغرض السكن.</div></td></tr>
    <tr><td style="padding: 10px 0;"><div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الثاني</div><div style="font-size: 14px; text-align: right; line-height: 1.8;">مدة الإيجار من <strong>{{START_DATE}}</strong> إلى <strong>{{END_DATE}}</strong>، تجديد الإقامة قبل الساعة 12 ظهراً، وفي حالة التأخير تفرض رسوم نصف يوم.</div></td></tr>
    <tr><td style="padding: 10px 0;"><div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الثالث</div><div style="font-size: 14px; text-align: right; line-height: 1.8;">إجمالي مبلغ الإيجار <strong>{{RENT_AMOUNT}} ريال</strong>، يُدفع قبل الإقامة.</div></td></tr>
    <tr><td style="padding: 10px 0;"><div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الرابع</div><div style="font-size: 14px; text-align: right; line-height: 1.8;">مبلغ تأمين <strong>{{DEPOSIT_AMOUNT}} ريال</strong> يرد عند التسليم لو الشقة سليمة من أي عيوب أو تكسير.</div></td></tr>
    <tr><td style="padding: 10px 0;"><div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الخامس</div><div style="font-size: 14px; text-align: right; line-height: 1.8;">الالتزام بمراعاة لائحة الذوق العام وعدم التسبب لأي مضايقة للجيران برفع الصوت أو أي فعل يمس براحة وخصوصية الجيران.</div></td></tr>
    <tr><td style="padding: 10px 0;"><div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند السادس</div><div style="font-size: 14px; text-align: right; line-height: 1.8;">يلتزم المستأجر بدفع كامل فترة الإقامة قبل تسجيل الوصول.</div></td></tr>
    <tr><td style="padding: 10px 0;"><div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند السابع</div><div style="font-size: 14px; text-align: right; line-height: 1.8;">المبلغ المدفوع لا يسترد نهائياً للحجز أو الإقامة.</div></td></tr>
    <tr><td style="padding: 10px 0;"><div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الثامن</div><div style="font-size: 14px; text-align: right; line-height: 1.8;">يمنع التأجير من الباطن بدون موافقة خطية من المؤجر وإلا اعتبر العقد ملغياً.</div></td></tr>
    <tr><td style="padding: 10px 0;"><div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند التاسع</div><div style="font-size: 14px; text-align: right; line-height: 1.8;">الالتزام باستخدام الإنترنت بشكل نظامي وعدم استخدامه لأي أنشطة مخالفة لأنظمة المملكة العربية السعودية.</div></td></tr>
    <tr><td style="padding: 10px 0;"><div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند العاشر</div><div style="font-size: 14px; text-align: right; line-height: 1.8;">موعد تسجيل الوصول 3 مساءً وتسجيل الخروج 12 مساءً.</div></td></tr>
    <tr><td style="padding: 10px 0;"><div style="text-align: center; color: #14532d; font-size: 16px; font-weight: bold; margin-bottom: 8px;">البند الحادي عشر</div><div style="font-size: 14px; text-align: right; line-height: 1.8;">يحق للمؤجر فسخ العقد وإخلاء الشقة فوراً دون تعويض عند إخلال المستأجر بأي بند من بنود هذا العقد.</div></td></tr>
</table>
<div style="text-align: center; font-weight: bold; color: #14532d; font-size: 16px; margin-bottom: 8px;">شروط إضافية</div>
<div style="background-color: #f9fafb; padding: 15px; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 14px; line-height: 1.8; color: #1f2937; margin-bottom: 20px; text-align: right;">{{ADDITIONAL_TERMS}}</div>
<div style="border-top: 3px solid #16a34a; margin-bottom: 20px;"></div>
<table style="width: 100%; border-collapse: separate; border-spacing: 20px 0;">
    <tr>
        <td style="width: 50%; background-color: #f0fdf4; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #bbf7d0;">
            <div style="color: #14532d; font-weight: bold; margin-bottom: 30px;">توقيع الطرف الأول (المؤجر)</div>
            {{LESSOR_SIGNATURE}}
        </td>
        <td style="width: 50%; background-color: #f0fdf4; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #bbf7d0;">
            <div style="color: #14532d; font-weight: bold; margin-bottom: 30px;">توقيع الطرف الثاني (المستأجر)</div>
            {{LESSEE_SIGNATURE}}
        </td>
    </tr>
</table>';
    }
}
