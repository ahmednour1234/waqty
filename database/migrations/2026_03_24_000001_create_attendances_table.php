<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('attendances')) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete()->index();
            $table->foreignId('shift_date_id')->nullable()->constrained('shift_dates')->nullOnDelete()->index();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('working_minutes')->nullable();
            $table->timestamps();

            // Prevent double check-in for the same shift date
            $table->unique(['employee_id', 'shift_date_id'], 'attendances_employee_shift_date_unique');

            $table->index(['employee_id', 'check_in_at'], 'attendances_employee_check_in_idx');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
