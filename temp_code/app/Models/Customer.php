<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'national_id', 'phone'];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
