<?php

namespace App\Services;

use App\Models\ProviderSubscription;
use App\Models\ServiceProvider;
use Illuminate\Support\Facades\DB;

class ProviderSubscriptionService
{
    /**
     * Activate or extend a monthly subscription for a service provider.
     */
    public function activate(
        ServiceProvider $provider,
        int $adminId,
        int $months = 1,
        float $amount = 0.00,
        ?string $reference = null,
        ?string $notes = null
    ): ProviderSubscription {
        $months = max(1, min(24, $months));

        return DB::transaction(function () use ($provider, $adminId, $months, $amount, $reference, $notes) {
            $locked = ServiceProvider::whereKey($provider->id)
                ->lockForUpdate()
                ->firstOrFail();

            // If currently active and unexpired, extend seamlessly from current end date
            $startsAt = ($locked->hasActiveSubscription() && $locked->subscription_ends_at)
                ? $locked->subscription_ends_at->copy()
                : now();

            $endsAt = $startsAt->copy()->addMonths($months);

            $planLabel = $months === 1
                ? 'اشتراك شهري (شهر واحد)'
                : "اشتراك ({$months} أشهر)";

            $subscription = ProviderSubscription::create([
                'service_provider_id' => $locked->id,
                'plan_name'           => $planLabel,
                'duration_months'     => $months,
                'amount'              => round($amount, 2),
                'starts_at'           => $startsAt,
                'ends_at'             => $endsAt,
                'status'              => 'active',
                'payment_reference'   => $reference,
                'notes'               => $notes,
                'activated_by'        => $adminId,
            ]);

            $locked->update([
                'is_active'              => true,
                'subscription_status'    => 'active',
                'subscription_starts_at' => $locked->subscription_starts_at && $locked->hasActiveSubscription()
                    ? $locked->subscription_starts_at
                    : $startsAt,
                'subscription_ends_at'   => $endsAt,
            ]);

            return $subscription;
        });
    }

    /**
     * Suspend/cancel a provider's active subscription so they cannot create new invoices.
     */
    public function suspend(ServiceProvider $provider, int $adminId, ?string $notes = null): void
    {
        DB::transaction(function () use ($provider, $notes) {
            $locked = ServiceProvider::whereKey($provider->id)
                ->lockForUpdate()
                ->firstOrFail();

            ProviderSubscription::where('service_provider_id', $locked->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'cancelled',
                    'notes'  => $notes ?: 'تم إيقاف الاشتراك من قبل الإدارة',
                ]);

            $locked->update([
                'subscription_status' => 'suspended',
            ]);
        });
    }
}
