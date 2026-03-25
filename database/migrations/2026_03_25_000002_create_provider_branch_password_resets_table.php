<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('provider_branch_password_resets')) {
            return;
        }

        Schema::create('provider_branch_password_resets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id', 'fk_pbpr_branch_id')
                  ->references('id')->on('provider_branches')
                  ->onDelete('cascade');
            $table->index('branch_id');
            $table->string('otp_hash', 255);
            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable();
            $table->smallInteger('attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->string('created_ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_branch_password_resets');
    }
};
