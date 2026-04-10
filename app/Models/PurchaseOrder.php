<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_name',
        'project_id',
        'status',
        'total_amount',
        'notes',
        'expected_date',
        'po_date',
        'created_by',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function debtsReceivables()
    {
        return $this->hasMany(DebtReceivable::class);
    }
}
