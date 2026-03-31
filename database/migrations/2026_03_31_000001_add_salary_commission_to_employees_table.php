<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('salary', 10, 2)->nullable()->after('logo_path');
            $table->decimal('commission_percentage', 5, 2)->nullable()->after('salary')->comment('Commission as a percentage (0-100)');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['salary', 'commission_percentage']);
        });
    }
};
