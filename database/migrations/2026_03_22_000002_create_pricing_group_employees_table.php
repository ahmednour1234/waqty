<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_group_employees', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->foreignId('pricing_group_id');
            $table->foreignId('employee_id');
            $table->timestamps();

            $table->foreign('pricing_group_id', 'pge_group_id_fk')
                ->references('id')->on('pricing_groups')->cascadeOnDelete();
            $table->foreign('employee_id', 'pge_employee_id_fk')
                ->references('id')->on('employees')->cascadeOnDelete();

            $table->unique(['pricing_group_id', 'employee_id'], 'pge_group_employee_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_group_employees');
    }
};
