<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Kode Project ex: CHERU1');
            $table->string('name');
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            $table->decimal('selling_price', 15, 2)->comment('Harga Jual / Nilai Kontrak');
            $table->decimal('estimated_hpp', 15, 2)->nullable()->comment('Total HPP hasil estimasi awal dari project_bom');
            $table->decimal('actual_hpp', 15, 2)->nullable()->comment('Total HPP aktual (diupdate otomatis dari cost summary)');
            $table->decimal('gross_profit', 15, 2)->nullable()->comment('selling_price - actual_hpp, diupdate otomatis');
            $table->string('status')->comment('pending, in_progress, done, canceled');
            $table->string('payment_status')->comment('unpaid, partial, paid');
            $table->date('start_date')->nullable();
            $table->date('deadline_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
