<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('banners')) {
            return;
        }

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->string('title');
            $table->string('image_path')->nullable();
            $table->enum('placement', [
                'home_top',
                'home_bottom',
                'home_middle',
                'category',
                'sidebar',
            ])->default('home_top')->index();
            $table->enum('dimensions', [
                '1200x400',
                '1200x600',
                '800x400',
                '600x300',
            ])->default('1200x400');
            $table->boolean('active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by_admin_id')->references('id')->on('admins')->onDelete('set null');

            $table->index(['active', 'starts_at', 'ends_at']);
            $table->index(['placement', 'active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
