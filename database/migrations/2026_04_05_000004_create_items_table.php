<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Kode Barang ex: HPLTH852J');
            $table->string('name');
            $table->string('unit')->comment('pcs, lembar, lot, mtr, kg');
            $table->integer('current_stock')->default(0)->comment('Total stok barang saat ini');
            $table->decimal('base_price', 15, 2)->comment('Harga beli rata-rata (moving average)');
            $table->integer('minimum_stock')->default(0)->comment('Batas stok minimum untuk alert');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
