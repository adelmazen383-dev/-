<?php

namespace App\Models;

use App\Enums\ProviderServiceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ProviderInvoice extends Model
{
    protected $table = 'provider_invoices';

    protected $fillable = [
        'service_provider_id',
        'invoice_number',
        'public_token',
        'service_type',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_national_id',
        'title',
        'description',
        'service_details',
        'amount',
        'agreed_amount',
        'deposit_amount',
        'paid_previously',
        'remaining_amount',
        'gateway_fee_percentage',
        'gateway_fee_amount',
        'total_payable',
        'payment_method',
        'payment_type',
        'status',
        'paid_at',
        'verified_at',
        'verified_by',
        'wallet_credited_at',
        'telr_order_ref',
        'telr_transaction_url',
        'tamara_order_id',
        'tamara_checkout_id',
        'tamara_order_reference_id',
        'tamara_status',
        'tamara_url',
        'notes',
        'quote_attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'service_details'        => 'array',
            'amount'                 => 'decimal:2',
            'agreed_amount'          => 'decimal:2',
            'deposit_amount'         => 'decimal:2',
            'paid_previously'        => 'decimal:2',
            'remaining_amount'       => 'decimal:2',
            'gateway_fee_percentage' => 'decimal:2',
            'gateway_fee_amount'     => 'decimal:2',
            'total_payable'          => 'decimal:2',
            'paid_at'                => 'datetime',
            'verified_at'            => 'datetime',
            'wallet_credited_at'     => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            if (empty($invoice->public_token)) {
                $invoice->public_token = Str::random(48);
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        do {
            $num = 'PRV-' . now()->format('Ym') . '-' . strtoupper(Str::random(5));
        } while (self::where('invoice_number', $num)->exists());

        return $num;
    }

    /* ── Relationships ── */

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

    public function verifiedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function walletTransaction(): HasOne
    {
        return $this->hasOne(ProviderWalletTransaction::class, 'provider_invoice_id');
    }

    /* ── State Helpers ── */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isWalletCredited(): bool
    {
        return $this->wallet_credited_at !== null;
    }

    public function serviceTypeEnum(): ProviderServiceType
    {
        return ProviderServiceType::tryFrom((string) $this->service_type) ?? ProviderServiceType::OTHER;
    }

    public function templateView(): string
    {
        return $this->serviceTypeEnum()->templateView();
    }

    public function getPublicPaymentUrlAttribute(): string
    {
        return route('provider-pay.show', $this->public_token);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid'      => 'مدفوعة ومؤكدة',
            'failed'    => 'فشلت العملية',
            'cancelled' => 'ملغاة',
            default     => 'بانتظار الدفع',
        };
    }

    public function paymentMethodCleanLabel(): string
    {
        return match ($this->payment_method) {
            'tabby'     => 'تابي (Tabby)',
            'tamara'    => 'تمارا (Tamara)',
            'apple_pay' => 'Apple Pay',
            'standard'  => 'بطاقة مدى / فيزا / ماستركارد',
            'cash'      => 'كاش (نقداً)',
            default     => 'الدفع الإلكتروني (جميع الوسائل)',
        };
    }

    public function paymentTypeLabel(): ?string
    {
        return match ($this->payment_type) {
            'full'    => 'مدفوع بالكامل',
            'partial' => 'دفع جزئي',
            default   => null,
        };
    }

    public function effectiveAgreedAmount(): float
    {
        $agreed = round((float) $this->agreed_amount, 2);
        return $agreed > 0 ? $agreed : round((float) $this->amount, 2);
    }

    public function effectiveRemainingAmount(): float
    {
        $agreed  = $this->effectiveAgreedAmount();
        $deposit = round((float) $this->deposit_amount, 2);
        $paid    = round((float) $this->paid_previously, 2);
        if ((float) $this->remaining_amount > 0 || $paid > 0 || $deposit > 0) {
            return max(0.0, round(($agreed + $deposit) - $paid, 2));
        }
        return round((float) $this->amount, 2);
    }

    public function calculateTotalForMethod(string $method): array
    {
        $base   = round((float) $this->amount, 2);
        $feePct = in_array($method, ['standard', 'apple_pay', 'tabby', 'tamara'], true)
            ? Setting::getFeePercentage($method)
            : 0.00;
        $feeAmt = $feePct > 0 ? round(($base * $feePct) / 100, 2) : 0.00;
        $total  = round($base + $feeAmt, 2);

        return [
            'base'           => $base,
            'fee_percentage' => $feePct,
            'fee_amount'     => $feeAmt,
            'total'          => $total,
        ];
    }
}
