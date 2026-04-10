<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'project_id',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_percent',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'invoice_type',
        'notes',
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

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function debtsReceivables()
    {
        return $this->hasMany(DebtReceivable::class);
    }
}
