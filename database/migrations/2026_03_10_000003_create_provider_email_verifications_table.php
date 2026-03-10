<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_email_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
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
        Schema::dropIfExists('provider_email_verifications');
    }
};
