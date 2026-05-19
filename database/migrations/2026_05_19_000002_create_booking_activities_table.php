<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_activities', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->unsignedBigInteger('booking_id');
            $table->string('event', 60);               // status_changed, created, payment_recorded, note_added, etc.
            $table->text('description')->nullable();
            $table->string('actor_type', 30)->nullable(); // provider | employee | system | user
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();   // snapshot of the actor's display name
            $table->json('metadata')->nullable();        // extra context, e.g. {from: 'confirmed', to: 'arrived'}
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->index(['booking_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_activities');
    }
};
