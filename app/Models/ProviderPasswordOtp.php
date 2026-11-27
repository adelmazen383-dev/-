<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderPasswordOtp extends Model
{
    protected $table = 'provider_password_otps';

    protected $fillable = [
        'service_provider_id',
        'email',
        'phone',
        'code_hash',
        'attempts',
        'expires_at',
        'used_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'attempts'   => 'integer',
            'expires_at' => 'datetime',
            'used_at'    => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }
}
