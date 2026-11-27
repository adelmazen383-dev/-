<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderWalletTransaction extends Model
{
    protected $table = 'provider_wallet_transactions';

    protected $fillable = [
        'service_provider_id',
        'provider_invoice_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference',
        'notes',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'amount'         => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after'  => 'decimal:2',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ProviderInvoice::class, 'provider_invoice_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'credit'     => 'إيداع فاتورة مدفوعة',
            'payout'     => 'تحويل وتصفير المحفظة',
            'adjustment' => 'تسوية إدارية',
            default      => $this->type,
        };
    }
}
