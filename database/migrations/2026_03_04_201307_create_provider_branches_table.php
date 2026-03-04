<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('provider_branches') && 
            Schema::hasColumn('provider_branches', 'id') && 
            Schema::hasColumn('provider_branches', 'provider_id') &&
            Schema::hasColumn('provider_branches', 'country_id') &&
            Schema::hasColumn('provider_branches', 'city_id')) {
            return;
        }

        if (Schema::hasTable('provider_branches')) {
            Schema::dropIfExists('provider_branches');
        }

        Schema::create('provider_branches', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade')->index();
            $table->string('name', 255);
            $table->string('phone', 30)->nullable();
            $table->foreignId('country_id')->constrained('countries')->onDelete('restrict')->index();
            $table->foreignId('city_id')->constrained('cities')->onDelete('restrict')->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->boolean('is_main')->default(false)->index();
            $table->boolean('active')->default(true)->index();
            $table->boolean('blocked')->default(false)->index();
            $table->boolean('banned')->default(false)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['provider_id', 'is_main']);
            $table->index(['provider_id', 'active', 'blocked', 'banned']);
            $table->index(['country_id', 'city_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_branches');
    }
};
