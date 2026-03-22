<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_groups', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->foreignId('provider_id');
            $table->foreign('provider_id', 'pg_provider_id_fk')
                ->references('id')->on('providers')->cascadeOnDelete();
            $table->json('name');
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['provider_id', 'active'], 'pg_provider_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_groups');
    }
};
