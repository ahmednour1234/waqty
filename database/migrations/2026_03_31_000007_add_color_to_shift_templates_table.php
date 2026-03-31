<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shift_templates', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('active')
                ->comment('Hex color code for calendar/timeline display, e.g. #FF5733');
        });
    }

    public function down(): void
    {
        Schema::table('shift_templates', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
