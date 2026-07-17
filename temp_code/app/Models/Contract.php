<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Contract extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'signed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'rent_amount' => 'decimal:2',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function template()
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    public function logs()
    {
        return $this->hasMany(ContractLog::class)->orderBy('created_at', 'desc');
    }

    public function signature()
    {
        return $this->hasOne(ContractSignature::class);
    }

    // Scopes for easy filtering
    public function scopeSigned($query) { return $query->where('status', 'signed'); }
    public function scopePending($query) { return $query->whereIn('status', ['draft', 'sent', 'viewed']); }
    public function scopeRejected($query) { return $query->where('status', 'rejected'); }
}
