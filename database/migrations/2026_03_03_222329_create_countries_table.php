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
        if (Schema::hasTable('countries') && Schema::hasColumn('countries', 'id')) {
            return;
        }

        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->json('name');
            $table->string('iso2', 2)->nullable()->unique();
            $table->string('phone_code', 10)->nullable();
            $table->boolean('active')->default(true)->index();
            $table->integer('sort_order')->default(0)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
