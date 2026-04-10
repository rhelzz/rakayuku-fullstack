<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->date('work_date');
            $table->string('work_type')->comment('harian, lembur, borongan');
            $table->string('description')->nullable()->comment('Keterangan pekerjaan hari ini');
            $table->decimal('hours_worked', 5, 2)->nullable()->comment('Jam kerja (untuk lembur)');
            $table->decimal('rate', 15, 2)->comment('Tarif yang digunakan (harian/lembur/borongan)');
            $table->decimal('amount', 15, 2)->comment('Total upah hari ini');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_logs');
    }
};
