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
            $table->foreignId('sub_category_id')->nullable()->constrained('subcategories')->nullOnDelete();
            $table->json('name');
            $table->string('image_path')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('sub_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
