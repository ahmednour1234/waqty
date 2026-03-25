<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('cities', 'governorate_id')) {
            return;
        }

        Schema::table('cities', function (Blueprint $table) {
            $table->unsignedBigInteger('governorate_id')->nullable()->after('country_id');
            $table->foreign('governorate_id')->references('id')->on('governorates')->onDelete('set null');
            $table->index('governorate_id');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropColumn('governorate_id');
        });
    }
};
