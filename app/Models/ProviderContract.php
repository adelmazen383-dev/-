<?php

namespace App\Models;

use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProviderContract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_provider_id',
        'provider_invoice_id',
        'template_id',
        'contract_number',
        'lessor_name',
        'lessor_national_id',
        'lessor_phone',
        'lessee_name',
        'lessee_national_id',
        'lessee_phone',
        'property_details',
        'start_date',
        'end_date',
        'rent_amount',
        'deposit_amount',
        'tourism_license_number',
        'additional_terms',
        'status',
        'verification_token',
        'pdf_path',
        'signed_pdf_path',
        'sent_at',
        'viewed_at',
        'signed_at',
        'rejected_at',
    ];

    protected $casts = [
        'start_date'     => 'datetime',
        'end_date'       => 'datetime',
        'sent_at'        => 'datetime',
        'viewed_at'      => 'datetime',
        'signed_at'      => 'datetime',
        'rejected_at'    => 'datetime',
        'rent_amount'    => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'status'         => ContractStatus::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $contract) {
            if (empty($contract->verification_token)) {
                $contract->verification_token = (string) Str::uuid();
            }
            if (empty($contract->status)) {
                $contract->status = ContractStatus::DRAFT;
            }
        });
    }

    public function provider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

    public function invoice()
    {
        return $this->belongsTo(ProviderInvoice::class, 'provider_invoice_id');
    }

    public function template()
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    public function signatures()
    {
        return $this->hasMany(ProviderContractSignature::class, 'provider_contract_id');
    }

    public function lesseeSignature()
    {
        return $this->hasOne(ProviderContractSignature::class, 'provider_contract_id')->where('role', 'lessee');
    }

    public function lessorSignature()
    {
        return $this->hasOne(ProviderContractSignature::class, 'provider_contract_id')->where('role', 'lessor');
    }

    public function logs()
    {
        return $this->hasMany(ProviderContractLog::class, 'provider_contract_id')->orderByDesc('created_at');
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    public function canBeSigned(): bool
    {
        return ! $this->isTerminal();
    }

    public function isExpired(): bool
    {
        return $this->end_date !== null && now()->greaterThan($this->end_date);
    }

    public function getSignUrlAttribute(): string
    {
        return route('provider-sign.show', $this->verification_token);
    }
}
