<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'project_id',
        'purchase_order_id',
        'transaction_type',
        'document_ref',
        'quantity',
        'balance_after',
        'unit_price',
        'total_price',
        'reference_note',
        'transaction_date',
        'created_by',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
