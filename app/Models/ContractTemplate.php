<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ContractTemplate extends Model
{
    protected $fillable = ['name', 'html_content', 'is_active'];
    public function contracts() { return $this->hasMany(Contract::class, 'template_id'); }
}
