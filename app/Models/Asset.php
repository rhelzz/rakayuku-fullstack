<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'coa_id',
        'purchase_date',
        'purchase_price',
        'depreciation_rate',
        'accumulated_depreciation',
        'book_value',
        'notes',
    ];

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }
}
