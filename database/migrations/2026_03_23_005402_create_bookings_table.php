<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 26)->unique();

            // Relationships
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('service_id');

            // Booking timing
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');

            // Pricing snapshot
            $table->decimal('price', 10, 2);
            $table->string('currency', 10)->default('SAR');

            // Status
            $table->string('status', 30)->default('pending');
            $table->string('payment_status', 30)->default('unpaid');

            // Notes
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Snapshots (JSON)
            $table->json('service_snapshot')->nullable();
            $table->json('employee_snapshot')->nullable();
            $table->json('branch_snapshot')->nullable();
            $table->json('provider_snapshot')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['provider_id', 'booking_date', 'status']);
            $table->index(['branch_id', 'booking_date', 'status']);
            $table->index(['employee_id', 'booking_date', 'start_time', 'status']);
            $table->index(['user_id', 'booking_date', 'status']);
            $table->index(['service_id', 'booking_date']);

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('provider_id')->references('id')->on('providers')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('provider_branches')->restrictOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->restrictOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
