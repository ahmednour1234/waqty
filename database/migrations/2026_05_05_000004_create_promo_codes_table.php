<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promo_codes')) {
            return;
        }

        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->string('code')->unique();
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 10, 2);
            $table->decimal('min_order', 10, 2)->default(0);
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->date('valid_until');
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by_admin_id')->references('id')->on('admins')->onDelete('set null');

            $table->index(['active', 'valid_until']);
            $table->index(['code', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
