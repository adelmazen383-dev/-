<?php
namespace App\Observers;

use App\Models\Contract;

class ContractObserver
{
    public function created(Contract $contract)
    {
        $contract->logs()->create([
            'event' => 'تم إنشاء العقد (مسودة)',
            'meta' => ['user_id' => auth()->id() ?? 'System']
        ]);
    }

    public function updated(Contract $contract)
    {
        if ($contract->isDirty('status')) {
            $original = $contract->getOriginal('status');
            $new = $contract->status;
            
            $statusMap = [
                'draft' => 'مسودة',
                'sent' => 'تم إرسال العقد للعميل',
                'viewed' => 'قام العميل بمشاهدة العقد',
                'signed' => 'تم توقيع العقد',
                'rejected' => 'قام العميل برفض العقد',
                'cancelled' => 'تم إلغاء العقد'
            ];

            $contract->logs()->create([
                'event' => 'تغيرت حالة العقد إلى: ' . ($statusMap[$new] ?? $new),
                'meta' => [
                    'from' => $original,
                    'to' => $new,
                    'user_id' => auth()->id() ?? 'System / Client'
                ]
            ]);
        }
    }
}
