<?php

namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ContractGeneratorService
{
    public function generateUniqueNumber(): string
    {
        return DB::transaction(function () {
            $prefix = config('rental.contract_prefix', 'RENT');
            $year = date('Y');

            $latest = Contract::lockForUpdate()
                ->where('contract_number', 'like', "{$prefix}-{$year}-%")
                ->orderByRaw('CAST(SUBSTR(contract_number, -6) AS INTEGER) DESC')
                ->first();

            $nextId = $latest ? ((int) substr($latest->contract_number, -6)) + 1 : 1;

            return sprintf('%s-%s-%06d', $prefix, $year, $nextId);
        });
    }

    private function createMpdf(): Mpdf
    {
        ini_set('max_execution_time', '120');
        ini_set('pcre.backtrack_limit', '10000000');

        $tempDir = storage_path('app/mpdf-temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'direction'     => 'rtl',
            'default_font'  => 'xbriyaz',
            'tempDir'       => $tempDir,
            'margin_top'    => 15,
            'margin_bottom' => 15,
            'margin_left'   => 15,
            'margin_right'  => 15,
        ]);

        // Speed up rendering
        $mpdf->simpleTables = true;
        $mpdf->packTableData = true;

        return $mpdf;
    }

    public function generateDraftPdf(Contract $contract): string
    {
        $html = $this->prepareHtml($contract);
        $html .= $this->getQrHtml($contract);
        $html = $this->wrapWithHtml($html);

        $mpdf = $this->createMpdf();
        $mpdf->WriteHTML($html);

        $fileName = 'contracts/drafts/' . $contract->contract_number . '_' . Str::random(5) . '.pdf';
        $fullPath = storage_path('app/public/' . $fileName);

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $mpdf->Output($fullPath, \Mpdf\Output\Destination::FILE);

        return 'storage/' . $fileName;
    }

    public function generateSignedPdf(Contract $contract): string
    {
        $html = $this->prepareHtml($contract);
        $html .= $this->getQrHtml($contract);
        $fullHtml = $this->wrapWithHtml($html);

        $mpdf = $this->createMpdf();
        $mpdf->WriteHTML($fullHtml);

        $fileName = 'contracts/signed/' . $contract->contract_number . '_signed_' . Str::random(5) . '.pdf';
        $fullPath = storage_path('app/public/' . $fileName);

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $mpdf->Output($fullPath, \Mpdf\Output\Destination::FILE);

        return 'storage/' . $fileName;
    }

    private function prepareHtml(Contract $contract): string
    {
        $html = $contract->template->html_content;

        $logoPath = public_path('images/logo.png');
        $logoHtml = '';
        if (file_exists($logoPath)) {
            $logoHtml = '<img src="' . $logoPath . '" style="max-height: 80px;" />';
        } else {
            $logoHtml = '<div style="font-size:24px; font-weight:bold; color:#14532d;">شعار الشركة</div>';
        }

        $additionalTerms = $contract->additional_terms ?? 'لا توجد شروط إضافية';
        // Prevent mPDF infinite loop on extremely long unbroken strings (like repeated dots or characters)
        $additionalTerms = preg_replace('/([^\s]{70})/u', '$1 ', $additionalTerms);

        // Signatures replacement
        $lessorHtml = '<div style="border-bottom: 1px solid #14532d; width: 80%; margin: 40px auto 10px auto;"></div>';
        $lesseeHtml = '<div style="border-bottom: 1px solid #14532d; width: 80%; margin: 40px auto 10px auto;"></div>';

        if ($contract->lessorSignature) {
            $sigContent = Storage::disk('public')->get(str_replace('storage/', '', $contract->lessorSignature->signature_path));
            $sigData = base64_encode($sigContent);
            $signedAt = $contract->lessorSignature->signed_at ? $contract->lessorSignature->signed_at->format('Y-m-d H:i:s') : '';
            $lessorHtml = '<img src="data:image/png;base64,' . $sigData . '" style="max-height:80px;" />'
                        . '<div style="font-size:10px; color:#6b7280; margin-top:5px;">IP: ' . e($contract->lessorSignature->ip_address) . '<br>الوقت: ' . $signedAt . '</div>';
        }

        if ($contract->lesseeSignature) {
            $sigContent = Storage::disk('public')->get(str_replace('storage/', '', $contract->lesseeSignature->signature_path));
            $sigData = base64_encode($sigContent);
            $signedAt = $contract->lesseeSignature->signed_at ? $contract->lesseeSignature->signed_at->format('Y-m-d H:i:s') : '';
            $lesseeHtml = '<img src="data:image/png;base64,' . $sigData . '" style="max-height:80px;" />'
                        . '<div style="font-size:10px; color:#6b7280; margin-top:5px;">IP: ' . e($contract->lesseeSignature->ip_address) . '<br>الوقت: ' . $signedAt . '</div>';
        }

        return str_replace(
            [
                '{{CONTRACT_NUMBER}}', '{{DATE}}', '{{LOGO_HTML}}',
                '{{CUSTOMER_NAME}}', '{{NATIONAL_ID}}', '{{PROPERTY_DETAILS}}',
                '{{RENT_AMOUNT}}', '{{START_DATE}}', '{{END_DATE}}',
                '{{TOURISM_LICENSE}}', '{{ADDITIONAL_TERMS}}',
                '{{LESSOR_SIGNATURE}}', '{{LESSEE_SIGNATURE}}',
                '{{LESSOR_NAME}}', '{{LESSOR_ID}}', '{{DEPOSIT_AMOUNT}}'
            ],
            [
                $contract->contract_number,
                now()->format('Y-m-d'),
                $logoHtml,
                e($contract->customer->name),
                e($contract->customer->national_id),
                e($contract->property_details),
                number_format($contract->rent_amount, 2),
                $contract->start_date->format('Y-m-d'),
                $contract->end_date->format('Y-m-d'),
                e($contract->tourism_license_number ?? 'لا يوجد'),
                nl2br(e($additionalTerms)),
                $lessorHtml,
                $lesseeHtml,
                e($contract->lessor->name ?? 'إدارة الأملاك'),
                e($contract->lessor->national_id ?? 'غير متوفر'),
                number_format($contract->deposit_amount ?? 500, 2),
            ],
            $html
        );
    }

    private function getQrHtml(Contract $contract): string
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
            . 'h1 { font-size: 26px; color: #254a34; margin: 0 0 10px 0; font-weight: bold; }'
            . 'h3 { font-size: 15px; color: white; background-color: #254a34; padding: 8px 15px; margin: 20px 0 10px 0; font-weight: bold; }'
            . 'hr { border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0; }'
            . '.invoice-table { width: 100%; border-collapse: collapse; margin-top: 20px; }'
            . '.invoice-table th { background-color: #254a34; color: white; text-align: right; padding: 12px 15px; font-size: 13px; font-weight: bold; border: 1px solid #254a34; }'
            . '.invoice-table td { background-color: #f8fafc; color: #1e293b; text-align: right; padding: 15px; border: 1px solid #e2e8f0; font-size: 14px; vertical-align: top; }'
            . '.total-section { margin-top: 20px; background-color: #f0fdf4; border-radius: 8px; padding: 15px; border-left: 4px solid #16a34a; }'
            . '.terms { background: #ffffff; padding: 15px; font-size: 12px; line-height: 1.8; word-wrap: break-word; overflow-wrap: break-word; border: 1px solid #e2e8f0; border-radius: 4px; }'
            . '.header-table td { border: none; padding: 0; background: transparent; }'
            . '.logo { max-height: 80px; }'
            . '</style></head><body>' . $body . '</body></html>';
    }
}
