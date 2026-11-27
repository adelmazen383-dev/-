<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSubscription extends Model
{
    protected $table = 'provider_subscriptions';

    protected $fillable = [
        'service_provider_id',
        'plan_name',
        'duration_months',
        'amount',
        'starts_at',
        'ends_at',
        'status',
        'payment_reference',
        'notes',
        'activated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'          => 'decimal:2',
            'duration_months' => 'integer',
            'starts_at'       => 'datetime',
            'ends_at'         => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

    public function activatedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }
}
