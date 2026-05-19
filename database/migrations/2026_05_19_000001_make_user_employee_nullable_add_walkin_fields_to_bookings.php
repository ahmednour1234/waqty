<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop existing foreign keys before altering columns
            $table->dropForeign(['user_id']);
            $table->dropForeign(['employee_id']);

            // Make nullable for walk-in / no-employee quick sales
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('employee_id')->nullable()->change();

            // Re-add foreign keys with nullOnDelete
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();

            // Walk-in customer fields
            $table->string('user_name')->nullable()->after('notes');
            $table->string('user_phone')->nullable()->after('user_name');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['employee_id']);

            $table->dropColumn(['user_name', 'user_phone']);

            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->unsignedBigInteger('employee_id')->nullable(false)->change();

            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->restrictOnDelete();
        });
    }
};
