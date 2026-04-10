<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtReceivable extends Model
{
    use HasFactory;

    protected $table = 'debts_receivables';

    protected $fillable = [
        'type',
        'entity_name',
        'project_id',
        'purchase_order_id',
        'invoice_id',
        'total_amount',
        'paid_amount',
        'due_date',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
