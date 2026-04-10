<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->comment('Jumlah yang dibayarkan pada termin ini');
            $table->date('payment_date');
            $table->string('payment_method')->comment('transfer, cash, cek');
            $table->string('wallet_type')->comment('kas_besar, kas_kecil, bank');
            $table->string('reference_no')->nullable()->comment('No. rekening / No. bukti transfer');
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
