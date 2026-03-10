<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('providers') && !Schema::hasColumn('providers', 'email_verified_at')) {
            Schema::table('providers', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('last_login_at');
            });
        }
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'email_verified_at')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('last_login_at');
            });
        }
        if (Schema::hasTable('admins') && !Schema::hasColumn('admins', 'email_verified_at')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('password');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('providers', 'email_verified_at')) {
            Schema::table('providers', fn (Blueprint $t) => $t->dropColumn('email_verified_at'));
        }
        if (Schema::hasColumn('employees', 'email_verified_at')) {
            Schema::table('employees', fn (Blueprint $t) => $t->dropColumn('email_verified_at'));
        }
        if (Schema::hasColumn('admins', 'email_verified_at')) {
            Schema::table('admins', fn (Blueprint $t) => $t->dropColumn('email_verified_at'));
        }
    }
};
