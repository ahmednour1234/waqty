<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_service', function (Blueprint $table) {
            $table->json('name')->nullable()->after('service_id');
            $table->json('description')->nullable()->after('name');
            $table->string('image_path')->nullable()->after('description');
            $table->foreignId('sub_category_id')->nullable()->after('image_path')
                ->constrained('subcategories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('provider_service', function (Blueprint $table) {
            $table->dropForeign(['sub_category_id']);
            $table->dropColumn(['name', 'description', 'image_path', 'sub_category_id']);
        });
    }
};
