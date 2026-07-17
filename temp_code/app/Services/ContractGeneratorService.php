<?php
namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContractGeneratorService
{
    public function generateUniqueNumber(): string
    {
        return DB::transaction(function () {
            $latest = Contract::lockForUpdate()->latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            return sprintf("RENT-%s-%06d", date('Y'), $nextId);
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

    public function generateSignedPdf(Contract $contract, string $signatureImagePath): string
    {
        $html = $this->prepareHtml($contract);
        
        $sigData = base64_encode(Storage::disk('public')->get(str_replace('storage/', '', $signatureImagePath)));
        
        $html .= '<br><hr><div style="text-align:center; margin-top: 30px;">';
        $html .= '<h3>توقيع الطرف الثاني (المستأجر)</h3>';
        $html .= '<img src="data:image/png;base64,'.$sigData.'" style="max-height:150px; border: 1px dashed #ccc; padding: 10px;" />';
        $html .= '<p style="font-size: 12px; color: #555;">IP: ' . ($contract->signature->ip_address ?? 'N/A') . ' | Time: ' . $contract->signed_at->format('Y-m-d H:i:s') . '</p>';
        $html .= '</div>';
        
        $html .= $this->getQrHtml($contract);

        $fileName = 'contracts/signed/' . $contract->contract_number . '_signed_' . Str::random(5) . '.pdf';
        $pdf = Pdf::loadHTML($this->wrapWithHtml($html));
        Storage::disk('public')->put($fileName, $pdf->output());
        
        return 'storage/' . $fileName;
    }

    private function prepareHtml(Contract $contract): string
    {
        $html = $contract->template->html_content;
        return str_replace(
            ['{{CUSTOMER_NAME}}', '{{NATIONAL_ID}}', '{{PROPERTY_DETAILS}}', '{{RENT_AMOUNT}}', '{{START_DATE}}', '{{END_DATE}}', '{{ADDITIONAL_TERMS}}'],
            [
                $contract->customer->name,
                $contract->customer->national_id,
                $contract->property_details,
                $contract->rent_amount,
                $contract->start_date->format('Y-m-d'),
                $contract->end_date->format('Y-m-d'),
                $contract->additional_terms ?? 'لا توجد شروط إضافية'
            ],
            $html
        );
    }

    private function getQrHtml(Contract $contract): string
    {
        $qrUrl = url('/verify?contract_number=' . $contract->contract_number);
        $qrCode = base64_encode(QrCode::format('svg')->size(120)->generate($qrUrl));
        return '<div style="text-align:center; margin-top:20px;"><p>امسح الرمز للتحقق من صحة العقد</p><img src="data:image/svg+xml;base64,'.$qrCode.'" /></div>';
    }

    private function wrapWithHtml(string $body): string
    {
        return '<html dir="rtl"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>body { font-family: "dejavu sans", sans-serif; line-height: 1.6; } h1,h2,h3 { color: #1e293b; }</style></head><body>' . $body . '</body></html>';
    }
}
