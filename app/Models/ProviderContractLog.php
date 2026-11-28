<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderContractLog extends Model
{
    protected $fillable = [
        'provider_contract_id',
        'event',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function contract()
    {
        return $this->belongsTo(ProviderContract::class, 'provider_contract_id');
    }
}
