<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_bom', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('estimated_qty', 10, 2)->comment('Kebutuhan bahan estimasi');
            $table->decimal('estimated_unit_price', 15, 2)->comment('Harga satuan estimasi saat planning');
            $table->decimal('estimated_total', 15, 2)->comment('estimated_qty * estimated_unit_price');
            $table->decimal('actual_qty', 10, 2)->nullable()->comment('Aktual pemakaian, diupdate dari inventory_transactions');
            $table->decimal('actual_total', 15, 2)->nullable()->comment('Aktual biaya bahan');
            $table->string('notes')->nullable()->comment('Catatan spesifikasi bahan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_bom');
    }
};
