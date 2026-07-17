<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'national_id', 'phone', 'email', 'address'];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Check if customer has any active (non-cancelled) contracts.
     */
    public function hasActiveContracts(): bool
    {
        return $this->contracts()
            ->whereNotIn('status', ['cancelled'])
            ->exists();
    }
}
