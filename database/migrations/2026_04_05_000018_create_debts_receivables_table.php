<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts_receivables', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('debt (Hutang ke supplier), receivable (Piutang dari klien)');
            $table->string('entity_name')->comment('Nama Supplier atau Klien');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete()->comment('Sumber hutang (dari PO)');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete()->comment('Sumber piutang (dari Invoice)');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status')->comment('unpaid, partial, paid');
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts_receivables');
    }
};
