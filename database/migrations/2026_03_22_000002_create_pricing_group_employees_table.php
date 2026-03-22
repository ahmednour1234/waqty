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
            $table->foreignId('pricing_group_id')->constrained('pricing_groups')->cascadeOnDelete()->index();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete()->index();
            $table->timestamps();

            $table->unique(['pricing_group_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_group_employees');
    }
};
