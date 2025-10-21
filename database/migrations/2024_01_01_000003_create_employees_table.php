<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('hire_date');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('position');
            $table->foreignId('work_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('zkteco_user_id')->nullable()->comment('ZKTeco device user ID');
            $table->string('nif', 9)->nullable()->comment('Portuguese Tax ID');
            $table->string('niss', 11)->nullable()->comment('Portuguese Social Security Number');
            $table->enum('contract_type', ['permanent', 'fixed_term', 'temporary'])->default('permanent');
            $table->decimal('weekly_hours', 5, 2)->default(40.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
