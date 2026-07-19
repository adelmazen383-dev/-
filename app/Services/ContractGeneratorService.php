<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function generateDraftPdf(Contract $contract): string
    {
        $html = $this->prepareHtml($contract);
        $html .= $this->getQrHtml($contract);

        $fileName = 'contracts/drafts/' . $contract->contract_number . '_' . Str::random(5) . '.pdf';
        $pdf = Pdf::loadHTML($this->wrapWithHtml($html));
        Storage::disk('public')->put($fileName, $pdf->output());

        return 'storage/' . $fileName;
    }

    public function generateSignedPdf(Contract $contract): string
    {
        $html = $this->prepareHtml($contract);

        $html .= '<br><hr><table style="width: 100%; margin-top: 30px; text-align: center;"><tr>';

        $lesseeSig = $contract->lesseeSignature;
        $lessorSig = $contract->lessorSignature;

        if ($lessorSig) {
            $sigContent = Storage::disk('public')->get(str_replace('storage/', '', $lessorSig->signature_path));
            $sigData = base64_encode($sigContent);
            $html .= '<td style="width: 50%; vertical-align: top;">';
            $html .= '<h3>توقيع الطرف الأول (المؤجر)</h3>';
            $html .= '<img src="data:image/png;base64,' . $sigData . '" style="max-height:120px; border: 1px dashed #ccc; padding: 10px;" />';
            $signedAt = $lessorSig->signed_at ? $lessorSig->signed_at->format('Y-m-d H:i:s') : '';
            $html .= '<p style="font-size: 10px; color: #555;">IP: ' . e($lessorSig->ip_address) . '<br>Time: ' . $signedAt . '</p>';
            $html .= '</td>';
        }

        if ($lesseeSig) {
            $sigContent = Storage::disk('public')->get(str_replace('storage/', '', $lesseeSig->signature_path));
            $sigData = base64_encode($sigContent);
            $html .= '<td style="width: 50%; vertical-align: top;">';
            $html .= '<h3>توقيع الطرف الثاني (المستأجر)</h3>';
            $html .= '<img src="data:image/png;base64,' . $sigData . '" style="max-height:120px; border: 1px dashed #ccc; padding: 10px;" />';
            $signedAt = $lesseeSig->signed_at ? $lesseeSig->signed_at->format('Y-m-d H:i:s') : '';
            $html .= '<p style="font-size: 10px; color: #555;">IP: ' . e($lesseeSig->ip_address) . '<br>Time: ' . $signedAt . '</p>';
            $html .= '</td>';
        }

        $html .= '</tr></table>';

        $html .= $this->getQrHtml($contract);

        $fileName = 'contracts/signed/' . $contract->contract_number . '_signed_' . Str::random(5) . '.pdf';
        $pdf = Pdf::loadHTML($this->wrapWithHtml($html));
        Storage::disk('public')->put($fileName, $pdf->output());

        return 'storage/' . $fileName;
    }

    private function prepareHtml(Contract $contract): string
    {
        $html = $contract->template->html_content;

        $paymentLabel = $contract->payment_method instanceof PaymentMethod
            ? $contract->payment_method->label()
            : ($contract->payment_method ?? 'غير محدد');

        return str_replace(
            [
                '{{CUSTOMER_NAME}}', '{{NATIONAL_ID}}', '{{PROPERTY_DETAILS}}',
                '{{RENT_AMOUNT}}', '{{START_DATE}}', '{{END_DATE}}',
                '{{PAYMENT_METHOD}}', '{{ADDITIONAL_TERMS}}',
            ],
            [
                e($contract->customer->name),
                e($contract->customer->national_id),
                e($contract->property_details),
                number_format($contract->rent_amount, 2),
                $contract->start_date->format('Y-m-d'),
                $contract->end_date->format('Y-m-d'),
                $paymentLabel,
                e($contract->additional_terms ?? 'لا توجد شروط إضافية'),
            ],
            $html
        );
    }

    private function getQrHtml(Contract $contract): string
    {
        $qrUrl = url('/verify?contract_number=' . $contract->contract_number);
        $qrCode = base64_encode(QrCode::format('svg')->size(120)->generate($qrUrl));

        return '<div style="text-align:center; margin-top:20px;">'
            . '<p>امسح الرمز للتحقق من صحة العقد</p>'
            . '<img src="data:image/svg+xml;base64,' . $qrCode . '" />'
            . '</div>';
    }

    private function wrapWithHtml(string $body): string
    {
        if (class_exists(\ArPHP\I18N\Arabic::class)) {
            $arabic = new \ArPHP\I18N\Arabic();
            $body = $arabic->utf8Glyphs($body);
        }

        return '<html dir="ltr"><head>'
            . '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'
            . '<style>'
            . 'body { font-family: "dejavu sans", sans-serif; line-height: 1.8; text-align: right; direction: ltr; }'
            . 'h1,h2,h3,p,div,td,th { text-align: right; direction: ltr; }'
            . 'h1 { font-size: 22px; color: #1e293b; margin-bottom: 20px; }'
            . 'h3 { font-size: 16px; color: #334155; margin-top: 20px; }'
            . 'hr { border: 1px solid #e2e8f0; margin: 20px 0; }'
            . '</style></head><body>' . $body . '</body></html>';
    }
}
