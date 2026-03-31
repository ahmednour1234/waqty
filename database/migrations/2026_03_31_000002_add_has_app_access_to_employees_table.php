<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('has_app_access')->default(false)->after('email_verified_at')->index();
        });

        // Backfill: employees that already have a password set have app access
        DB::table('employees')
            ->whereNotNull('password')
            ->where('password', '!=', '')
            ->update(['has_app_access' => true]);
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['has_app_access']);
            $table->dropColumn('has_app_access');
        });
    }
};
