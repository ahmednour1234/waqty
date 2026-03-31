<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop the existing strict foreign key and re-add as nullable
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'booking_date', 'status']);

            $table->unsignedBigInteger('user_id')->nullable()->change();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['user_id', 'booking_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'booking_date', 'status']);

            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->index(['user_id', 'booking_date', 'status']);
        });
    }
};
