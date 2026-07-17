<?php

namespace App\Observers;

use App\Enums\ContractStatus;
use App\Models\Contract;

class ContractObserver
{
    public function created(Contract $contract): void
    {
        $contract->logs()->create([
            'event' => 'تم إنشاء العقد (مسودة)',
            'meta'  => ['user_id' => auth()->id()],
        ]);
    }

    /**
     * Fix #16: Handle auth()->id() properly for public vs admin routes.
     */
    public function updated(Contract $contract): void
    {
        if ($contract->isDirty('status')) {
            $new = $contract->status;

            // Get label from Enum if it's cast, otherwise fallback
            $label = $new instanceof ContractStatus
                ? $new->label()
                : ($new ?? 'غير معروف');

            $contract->logs()->create([
                'event' => 'تغيرت حالة العقد إلى: ' . $label,
                'meta'  => [
                    'from'    => $contract->getOriginal('status'),
                    'to'      => $new instanceof ContractStatus ? $new->value : $new,
                    'user_id' => auth()->id(), // null for public routes (customer actions)
                ],
            ]);
        }
    }
}
