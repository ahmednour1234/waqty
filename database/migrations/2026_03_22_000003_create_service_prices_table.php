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
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete()->index();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete()->index();
            $table->foreignId('branch_id')->nullable()->constrained('provider_branches')->nullOnDelete()->index();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete()->index();
            $table->foreignId('pricing_group_id')->nullable()->constrained('pricing_groups')->nullOnDelete()->index();
            $table->decimal('price', 12, 2)->unsigned();
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();

            // Composite indexes for fast resolver queries
            $table->index(['provider_id', 'service_id', 'active']);
            $table->index(['service_id', 'branch_id', 'active']);
            $table->index(['service_id', 'employee_id', 'active']);
            $table->index(['service_id', 'pricing_group_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_prices');
    }
};
