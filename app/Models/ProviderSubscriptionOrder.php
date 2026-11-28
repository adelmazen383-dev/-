<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProviderSubscriptionOrder extends Model
{
    public const STATUS_PENDING_PAYMENT       = 'pending_payment';
    public const STATUS_PAID_PENDING_APPROVAL = 'paid_pending_approval';
    public const STATUS_APPROVED              = 'approved';
    public const STATUS_REJECTED              = 'rejected';
    public const STATUS_FAILED                = 'failed';

    protected $fillable = [
        'order_number',
        'public_token',
        'service_provider_id',
        'provider_subscription_id',
        'duration_months',
        'plan_name',
        'amount',
        'payment_method',
        'status',
        'telr_order_ref',
        'telr_transaction_url',
        'tamara_order_id',
        'tamara_checkout_id',
        'tamara_order_reference_id',
        'tamara_status',
        'tamara_url',
        'paid_at',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    protected $casts = [
        'duration_months' => 'integer',
        'amount'          => 'decimal:2',
        'paid_at'         => 'datetime',
        'approved_at'     => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $order) {
            if (empty($order->public_token)) {
                $order->public_token = (string) Str::uuid();
            }
            if (empty($order->order_number)) {
                $order->order_number = 'PSUB-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            }
            if (empty($order->status)) {
                $order->status = self::STATUS_PENDING_PAYMENT;
            }
        });
    }

    public function provider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

    public function subscription()
    {
        return $this->belongsTo(ProviderSubscription::class, 'provider_subscription_id');
    }

    public function approvedByAdmin()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPendingPayment(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function isPaidPendingApproval(): bool
    {
        return $this->status === self::STATUS_PAID_PENDING_APPROVAL;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_PAYMENT       => 'بانتظار إتمام الدفع',
            self::STATUS_PAID_PENDING_APPROVAL => 'مدفوع — بانتظار موافقة الإدارة',
            self::STATUS_APPROVED              => 'معتمد ومفعّل',
            self::STATUS_REJECTED              => 'مرفوض من الإدارة',
            self::STATUS_FAILED                => 'فشل / ملغى',
            default                            => $this->status,
        };
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'tamara' => 'تمارا (4 دفعات)',
            default  => 'دفع إلكتروني (بطاقة / مدى / Apple Pay)',
        };
    }
}
