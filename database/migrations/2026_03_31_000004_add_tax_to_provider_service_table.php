<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_service', function (Blueprint $table) {
            $table->boolean('tax_enabled')->default(false)->after('estimated_duration_minutes');
            $table->decimal('tax_percentage', 5, 2)->nullable()->after('tax_enabled')
                ->comment('Tax as a percentage (0-100)');
        });
    }

    public function down(): void
    {
        Schema::table('provider_service', function (Blueprint $table) {
            $table->dropColumn(['tax_enabled', 'tax_percentage']);
        });
    }
};
