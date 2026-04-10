<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete()->comment('Null jika untuk stok umum');
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete()->comment('Link ke PO jika transaksi masuk dari pembelian');
            $table->string('transaction_type')->comment('in (Masuk), out (Keluar), return (Retur), adjustment (Penyesuaian)');
            $table->string('document_ref')->comment('No. Dokumen ex: PO-001, SJ-023, BA-05');
            $table->integer('quantity')->comment('Jumlah mutasi barang (selalu positif, arah dari transaction_type)');
            $table->integer('balance_after')->comment('Sisa stok setelah transaksi ini (Running Balance / Kartu Stok)');
            $table->decimal('unit_price', 15, 2)->comment('Harga per item saat transaksi');
            $table->decimal('total_price', 15, 2)->comment('quantity * unit_price');
            $table->string('reference_note')->nullable()->comment('Nama Supplier, Subkon, atau Keterangan');
            $table->date('transaction_date');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('item_id');
            $table->index('project_id');
            $table->index('transaction_date');
            $table->index(['item_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
