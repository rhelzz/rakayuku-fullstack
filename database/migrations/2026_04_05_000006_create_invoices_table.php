<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique()->comment('No. Invoice ex: INV-2024-001');
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_percent', 5, 2)->default(0)->comment('Persentase PPN, 0 jika tidak kena pajak');
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->comment('subtotal - discount + tax');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('Total yang sudah dibayar');
            $table->decimal('remaining_amount', 15, 2)->comment('total_amount - paid_amount');
            $table->string('status')->comment('draft, sent, partial, paid, overdue, canceled');
            $table->string('invoice_type')->comment('dp (Down Payment), progress, final');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
