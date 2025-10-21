<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., 'vacation', 'maternity'
            $table->string('name'); // Portuguese name, e.g., 'Férias'
            $table->text('description')->nullable(); // Description including legal article reference
            $table->integer('days_entitled')->nullable(); // Number of days entitled per year/occurrence
            $table->integer('min_days')->nullable(); // Minimum days (e.g., maternity 120 min)
            $table->integer('max_days')->nullable(); // Maximum days (e.g., maternity 150 max)
            $table->boolean('is_paid')->default(true); // Whether the leave is paid
            $table->boolean('requires_approval')->default(true); // Whether requires manager approval
            $table->boolean('is_active')->default(true); // Whether this leave type is currently active
            $table->json('metadata')->nullable(); // Additional configuration as JSON
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
