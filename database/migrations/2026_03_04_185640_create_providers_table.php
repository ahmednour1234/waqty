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
        if (Schema::hasTable('providers')) {
            return;
        }

        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('code', 50)->nullable()->unique();
            $table->string('phone', 30)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null')->index();
            $table->foreignId('country_id')->constrained('countries')->onDelete('restrict')->index();
            $table->foreignId('city_id')->constrained('cities')->onDelete('restrict')->index();
            $table->boolean('active')->default(false)->index();
            $table->boolean('blocked')->default(false)->index();
            $table->boolean('banned')->default(false)->index();
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['active', 'blocked', 'banned']);
            $table->index(['country_id', 'city_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
