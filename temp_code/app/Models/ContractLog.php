<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ContractLog extends Model
{
    protected $fillable = ['contract_id', 'event', 'meta'];
    protected $casts = ['meta' => 'array'];
    public function contract() { return $this->belongsTo(Contract::class); }
}
