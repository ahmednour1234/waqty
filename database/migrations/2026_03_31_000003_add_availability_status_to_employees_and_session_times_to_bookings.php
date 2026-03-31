<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add availability tracking to employees
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('availability_status', ['available', 'in_session', 'break', 'off'])
                ->default('available')
                ->after('has_app_access')
                ->index();
            $table->timestamp('availability_updated_at')->nullable()->after('availability_status');
        });

        // Add session timing to bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('session_started_at')->nullable()->after('cancelled_at');
            $table->timestamp('session_ended_at')->nullable()->after('session_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['availability_status']);
            $table->dropColumn(['availability_status', 'availability_updated_at']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['session_started_at', 'session_ended_at']);
        });
    }
};
