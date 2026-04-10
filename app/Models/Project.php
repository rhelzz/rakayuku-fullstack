<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'customer_name',
        'customer_phone',
        'customer_address',
        'selling_price',
        'estimated_hpp',
        'actual_hpp',
        'gross_profit',
        'status',
        'payment_status',
        'start_date',
        'deadline_date',
        'notes',
    ];

    public function projectBoms()
    {
        return $this->hasMany(ProjectBom::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function debtsReceivables()
    {
        return $this->hasMany(DebtReceivable::class);
    }

    public function projectCostSummary()
    {
        return $this->hasOne(ProjectCostSummary::class);
    }
}
