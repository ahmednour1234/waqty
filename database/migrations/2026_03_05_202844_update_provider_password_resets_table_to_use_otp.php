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
        Schema::table('provider_password_resets', function (Blueprint $table) {
            $table->dropColumn('token_hash');
            $table->string('otp_hash', 255)->after('provider_id');
            $table->smallInteger('attempts')->default(0)->after('otp_hash');
            $table->timestamp('locked_until')->nullable()->after('attempts');
        });
    }

    public function down(): void
    {
        Schema::table('provider_password_resets', function (Blueprint $table) {
            $table->dropColumn(['otp_hash', 'attempts', 'locked_until']);
            $table->string('token_hash', 255)->after('provider_id');
        });
    }
};
