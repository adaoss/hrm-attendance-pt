<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->integer('month'); // 1-12
            $table->decimal('base_salary', 10, 2);
            $table->decimal('regular_hours_worked', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('gross_pay', 10, 2);
            $table->decimal('net_pay', 10, 2);
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'calculated', 'paid'])->default('pending');
            $table->json('breakdown')->nullable(); // Detailed breakdown as JSON
            $table->timestamps();

            // Ensure one wage record per employee per month
            $table->unique(['employee_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wages');
    }
};
