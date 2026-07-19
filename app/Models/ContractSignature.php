<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ContractSignature extends Model
{
    protected $fillable = ['contract_id', 'role', 'signature_path', 'ip_address', 'user_agent', 'signed_at'];
    protected $casts = ['signed_at' => 'datetime'];
    public function contract() { return $this->belongsTo(Contract::class); }
}
