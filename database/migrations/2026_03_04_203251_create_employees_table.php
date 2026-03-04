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
        if (Schema::hasTable('employees') && 
            Schema::hasColumn('employees', 'id') && 
            Schema::hasColumn('employees', 'provider_id')) {
            return;
        }

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade')->index();
            $table->foreignId('branch_id')->constrained('provider_branches')->onDelete('restrict')->index();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('phone', 30)->nullable();
            $table->string('password', 255);
            $table->string('logo_path', 255)->nullable();
            $table->boolean('active')->default(true)->index();
            $table->boolean('blocked')->default(false)->index();
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['provider_id', 'branch_id', 'active', 'blocked']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
