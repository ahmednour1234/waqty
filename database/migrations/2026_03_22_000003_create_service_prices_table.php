<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_prices', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->foreignId('provider_id');
            $table->foreignId('service_id');
            $table->foreignId('branch_id')->nullable();
            $table->foreignId('employee_id')->nullable();
            $table->foreignId('pricing_group_id')->nullable();
            $table->decimal('price', 12, 2)->unsigned();
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('provider_id', 'sp_provider_id_fk')
                ->references('id')->on('providers')->cascadeOnDelete();
            $table->foreign('service_id', 'sp_service_id_fk')
                ->references('id')->on('services')->cascadeOnDelete();
            $table->foreign('branch_id', 'sp_branch_id_fk')
                ->references('id')->on('provider_branches')->nullOnDelete();
            $table->foreign('employee_id', 'sp_employee_id_fk')
                ->references('id')->on('employees')->nullOnDelete();
            $table->foreign('pricing_group_id', 'sp_pricing_group_id_fk')
                ->references('id')->on('pricing_groups')->nullOnDelete();

            // Composite indexes for fast resolver queries
            $table->index(['provider_id', 'service_id', 'active'], 'sp_provider_service_active_idx');
            $table->index(['service_id', 'branch_id', 'active'], 'sp_service_branch_active_idx');
            $table->index(['service_id', 'employee_id', 'active'], 'sp_service_employee_active_idx');
            $table->index(['service_id', 'pricing_group_id', 'active'], 'sp_service_group_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_prices');
    }
};
