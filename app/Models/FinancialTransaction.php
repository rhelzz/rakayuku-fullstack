<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'coa_id',
        'wallet_type',
        'transaction_type',
        'amount',
        'project_id',
        'purchase_order_id',
        'invoice_payment_id',
        'document_ref',
        'description',
        'transaction_date',
        'created_by',
    ];

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function invoicePayment()
    {
        return $this->belongsTo(InvoicePayment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
