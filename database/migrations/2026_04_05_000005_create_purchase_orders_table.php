<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique()->comment('No. PO ex: PO-2024-001');
            $table->string('supplier_name');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete()->comment('Null jika pembelian untuk stok umum');
            $table->string('status')->comment('draft, sent, partial_received, received, canceled');
            $table->decimal('total_amount', 15, 2);
            $table->text('notes')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('po_date');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
