<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_cost_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->cascadeOnDelete();
            $table->decimal('hpp_material', 15, 2)->default(0);
            $table->decimal('hpp_labor', 15, 2)->default(0);
            $table->decimal('hpp_overhead', 15, 2)->default(0);
            $table->decimal('hpp_total', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->decimal('gross_margin_percent', 5, 2)->default(0);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->index('last_updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_cost_summary');
    }
};
