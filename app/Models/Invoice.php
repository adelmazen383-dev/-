<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template',
        'invoice_number',
        'customer_name',
        'phone',
        'national_id',
        'apartment_number',
        'suite_number',
        'property_name',
        'property_location',
        'property_phone',
        'check_in_date',
        'check_out_date',
        'amount',
        'agreed_amount',
        'amount_paid',
        'remaining_amount',
        'deposit_amount',
        'status',
        'telr_order_ref',
        'telr_transaction_url',
        'booking_card_token',
        'notes',
        'paid_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isSindi(): bool
    {
        return $this->template === 'sindi' || ($this->user && $this->user->isSindi());
    }

    protected $casts = [
        'amount'           => 'decimal:2',
        'agreed_amount'    => 'decimal:2',
        'amount_paid'      => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'deposit_amount'   => 'decimal:2',
        'check_in_date'    => 'date',
        'check_out_date'   => 'date',
        'paid_at'          => 'datetime',
    ];

    /* ── Boot ── */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->booking_card_token)) {
                $invoice->booking_card_token = Str::random(48);
            }
        });
    }

    /* ── Status helpers ── */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === 'partial';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'بانتظار الدفع',
            'paid'      => 'مدفوعة',
            'partial'   => 'مدفوعة جزئياً',
            'failed'    => 'فشلت',
            'cancelled' => 'ملغاة',
            default     => $this->status,
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending'   => 'bg-amber-500/10 text-amber-500 border border-amber-500/20',
            'paid'      => 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20',
            'partial'   => 'bg-blue-500/10 text-blue-500 border border-blue-500/20',
            'failed'    => 'bg-rose-500/10 text-rose-500 border border-rose-500/20',
            'cancelled' => 'bg-slate-500/10 text-slate-500 border border-slate-500/20',
            default     => 'bg-slate-500/10 text-slate-500 border border-slate-500/20',
        };
    }

    /* ── Computed ── */

    public function getNightsCountAttribute(): int
    {
        if ($this->check_in_date && $this->check_out_date) {
            return $this->check_in_date->diffInDays($this->check_out_date);
        }
        return 0;
    }

    public function getBookingCardUrlAttribute(): string
    {
        return route('booking-card.show', $this->booking_card_token ?? 'invalid');
    }
}
