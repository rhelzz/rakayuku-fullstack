<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coa_id')->constrained('chart_of_accounts')->restrictOnDelete()->comment('Kategori Biaya/Pemasukan');
            $table->string('wallet_type')->comment('kas_besar, kas_kecil, bank');
            $table->string('transaction_type')->comment('income, expense');
            $table->decimal('amount', 15, 2);
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete()->comment('Terkait project jika ada');
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete()->comment('Terkait PO jika ada (bayar supplier)');
            $table->foreignId('invoice_payment_id')->nullable()->constrained('invoice_payments')->nullOnDelete()->comment('Terkait penerimaan dari klien jika ada');
            $table->string('document_ref')->nullable()->comment('No. Dokumen ex: INV-001, PO-023, KK-05');
            $table->text('description')->comment('Uraian pengeluaran/pemasukan');
            $table->date('transaction_date');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
