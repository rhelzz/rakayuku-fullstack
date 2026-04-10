<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Mesin Cutting, Mobil Pick Up');
            $table->string('category');
            $table->foreignId('coa_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete()->comment('Akun aset di CoA untuk pencatatan penyusutan');
            $table->date('purchase_date');
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('depreciation_rate', 5, 2)->comment('Persentase penyusutan per tahun');
            $table->decimal('accumulated_depreciation', 15, 2)->default(0)->comment('Total penyusutan yang sudah dicatat');
            $table->decimal('book_value', 15, 2)->nullable()->comment('purchase_price - accumulated_depreciation');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
