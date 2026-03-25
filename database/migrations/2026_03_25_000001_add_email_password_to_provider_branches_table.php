<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('provider_branches', 'email')) {
            return;
        }

        Schema::table('provider_branches', function (Blueprint $table) {
            $table->string('email', 191)->nullable()->unique()->after('phone');
            $table->string('password', 255)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('provider_branches', function (Blueprint $table) {
            $table->dropColumn(['email', 'password']);
        });
    }
};
