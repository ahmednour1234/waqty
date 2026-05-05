<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('content_pages')) {
            return;
        }

        Schema::create('content_pages', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->string('slug')->unique();
            $table->string('title_en');
            $table->string('title_ar');
            $table->longText('content_en')->nullable();
            $table->longText('content_ar')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->unsignedBigInteger('updated_by_admin_id')->nullable();
            $table->timestamps();

            $table->foreign('updated_by_admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_pages');
    }
};
