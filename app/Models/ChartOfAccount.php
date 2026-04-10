<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
    ];

    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'coa_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'coa_id');
    }
}
