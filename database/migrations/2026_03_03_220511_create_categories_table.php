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
        if (Schema::hasTable('categories')) {
            return;
        }

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->json('name');
            $table->string('slug')->nullable();
            $table->string('image_path')->nullable();
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
        Schema::dropIfExists('categories');
    }
};
