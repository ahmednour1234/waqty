<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('sub_category_id')->constrained('subcategories')->cascadeOnDelete();
            $table->json('name');
            $table->string('image_path')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['provider_id', 'active']);
            $table->index(['sub_category_id', 'active']);
            $table->index(['provider_id', 'sub_category_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
