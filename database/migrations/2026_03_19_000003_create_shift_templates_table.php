<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shift_templates', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete()->index();
            $table->json('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['provider_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_templates');
    }
};
