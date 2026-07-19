<?php

namespace App\Models;

use App\Enums\ContractStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Contract extends Model
{
    use SoftDeletes;
    /**
     * Fix #1: Explicit fillable instead of $guarded = []
     * Only fields that should be mass-assignable from forms/controllers.
     */
    protected $fillable = [
        'customer_id',
        'lessor_id',
        'template_id',
        'property_details',
        'start_date',
        'end_date',
        'rent_amount',
        'site_profit',
        'payment_method',
        'additional_terms',
        'created_by',
        'contract_number',
        'status',
        'verification_token',
        'pdf_path',
        'signed_pdf_path',
        'qr_path',
        'sent_at',
        'viewed_at',
        'signed_at',
        'rejected_at',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'sent_at'     => 'datetime',
        'viewed_at'   => 'datetime',
        'signed_at'   => 'datetime',
        'rejected_at' => 'datetime',
        'rent_amount' => 'decimal:2',
        'site_profit' => 'decimal:2',
        'status'      => ContractStatus::class,
        'payment_method' => PaymentMethod::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($contract) {
            if (empty($contract->verification_token)) {
                $contract->verification_token = (string) Str::uuid();
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function lessor()
    {
        return $this->belongsTo(Customer::class, 'lessor_id');
    }

    public function template()
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    public function logs()
    {
        return $this->hasMany(ContractLog::class)->orderBy('created_at', 'desc');
    }

    public function signatures()
    {
        return $this->hasMany(ContractSignature::class);
    }

    public function lesseeSignature()
    {
        return $this->hasOne(ContractSignature::class)->where('role', 'lessee');
    }

    public function lessorSignature()
    {
        return $this->hasOne(ContractSignature::class)->where('role', 'lessor');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ─────────────────────────────────────────────

    public function scopeSigned($query)
    {
        return $query->where('status', ContractStatus::SIGNED);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ContractStatus::pendingStates());
    }

    public function scopeRejected($query)
    {
        return $query->where('status', ContractStatus::REJECTED);
    }

    // ─── Helpers ─────────────────────────────────────────────

    /**
     * Check if the contract is in a terminal (final) state.
     */
    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    /**
     * Check if the contract can be signed by the customer.
     */
    public function canBeSigned(): bool
    {
        return !$this->isTerminal();
    }
}
