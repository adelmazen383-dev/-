<?php

namespace App\Services;

use App\Models\ProviderInvoice;
use App\Models\ProviderWalletTransaction;
use App\Models\ServiceProvider;
use Illuminate\Support\Facades\DB;

class ProviderWalletService
{
    /**
     * Atomically credit provider wallet for a verified paid invoice (strictly idempotent).
     */
    public function creditFromInvoice(ProviderInvoice $invoice, ?int $adminId = null): ?ProviderWalletTransaction
    {
        return DB::transaction(function () use ($invoice, $adminId) {
            $lockedInvoice = ProviderInvoice::whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Strictly prevent double-crediting even under concurrent callback + webhook
            if ($lockedInvoice->wallet_credited_at !== null) {
                return ProviderWalletTransaction::where('provider_invoice_id', $lockedInvoice->id)
                    ->where('type', 'credit')
                    ->first();
            }

            if ($lockedInvoice->status !== 'paid') {
                return null;
            }

            $provider = ServiceProvider::whereKey($lockedInvoice->service_provider_id)
                ->lockForUpdate()
                ->firstOrFail();

            $creditAmount  = round((float) $lockedInvoice->amount, 2);
            $balanceBefore = round((float) $provider->wallet_balance, 2);
            $balanceAfter  = round($balanceBefore + $creditAmount, 2);

            $provider->update([
                'wallet_balance' => $balanceAfter,
                'total_earned'   => round((float) $provider->total_earned + $creditAmount, 2),
            ]);

            $lockedInvoice->update([
                'wallet_credited_at' => now(),
            ]);

            return ProviderWalletTransaction::create([
                'service_provider_id' => $provider->id,
                'provider_invoice_id' => $lockedInvoice->id,
                'type'                => 'credit',
                'amount'              => $creditAmount,
                'balance_before'      => $balanceBefore,
                'balance_after'       => $balanceAfter,
                'reference'           => $lockedInvoice->invoice_number,
                'notes'               => 'إضافة رصيد فاتورة مدفوعة ومؤكدة رقم ' . $lockedInvoice->invoice_number . ' للعميل ' . $lockedInvoice->customer_name,
                'created_by_admin_id' => $adminId,
            ]);
        });
    }

    /**
     * Admin transfers funds to provider and zeroes (or partially settles) their wallet balance.
     */
    public function settleWallet(
        ServiceProvider $provider,
        int $adminId,
        ?float $amount = null,
        ?string $reference = null,
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($provider, $adminId, $amount, $reference, $notes) {
            $locked = ServiceProvider::whereKey($provider->id)
                ->lockForUpdate()
                ->firstOrFail();

            $balanceBefore = round((float) $locked->wallet_balance, 2);
            if ($balanceBefore <= 0) {
                return [
                    'success' => false,
                    'error'   => 'رصيد محفظة مقدم الخدمة يساوي 0.00 ر.س حالياً ولا يوجد مبلغ للتحويل.',
                ];
            }

            $settleAmount = $amount !== null ? round($amount, 2) : $balanceBefore;
            if ($settleAmount <= 0 || $settleAmount > $balanceBefore) {
                return [
                    'success' => false,
                    'error'   => 'مبلغ التحويل غير صالح. يجب أن يكون أكبر من صفر ولا يتجاوز الرصيد الحالي (' . number_format($balanceBefore, 2) . ' ر.س).',
                ];
            }

            $balanceAfter = round($balanceBefore - $settleAmount, 2);

            $locked->update([
                'wallet_balance' => $balanceAfter,
                'total_settled'  => round((float) $locked->total_settled + $settleAmount, 2),
            ]);

            $tx = ProviderWalletTransaction::create([
                'service_provider_id' => $locked->id,
                'type'                => 'payout',
                'amount'              => $settleAmount,
                'balance_before'      => $balanceBefore,
                'balance_after'       => $balanceAfter,
                'reference'           => $reference ?: ('PAYOUT-' . now()->format('Ymd-His')),
                'notes'               => $notes ?: ($balanceAfter <= 0.0 ? 'تحويل كامل الرصيد وتصفير المحفظة' : 'تحويل جزئي من رصيد المحفظة'),
                'created_by_admin_id' => $adminId,
            ]);

            return [
                'success'     => true,
                'transaction' => $tx,
                'amount'      => $settleAmount,
                'balance_after' => $balanceAfter,
            ];
        });
    }
}
