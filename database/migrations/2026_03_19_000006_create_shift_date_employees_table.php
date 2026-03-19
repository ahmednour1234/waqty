<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('shift_date_employees')) {
            return;
        }

        Schema::create('shift_date_employees', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->foreignId('shift_date_id')->constrained('shift_dates')->cascadeOnDelete()->index();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete()->index();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->unique(['shift_date_id', 'employee_id']);
            $table->index(['shift_date_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_date_employees');
    }
};
