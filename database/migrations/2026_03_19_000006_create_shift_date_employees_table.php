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

        Schema::disableForeignKeyConstraints();

        Schema::create('shift_date_employees', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->unsignedBigInteger('shift_date_id')->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->foreign('shift_date_id', 'sde_shift_date_id_fk')->references('id')->on('shift_dates')->cascadeOnDelete();
            $table->foreign('employee_id', 'sde_employee_id_fk')->references('id')->on('employees')->cascadeOnDelete();

            $table->unique(['shift_date_id', 'employee_id']);
            $table->index(['shift_date_id', 'employee_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_date_employees');
    }
};
