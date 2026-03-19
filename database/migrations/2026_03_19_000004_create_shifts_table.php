<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete()->index();
            $table->foreignId('branch_id')->nullable()->constrained('provider_branches')->nullOnDelete()->index();
            $table->foreignId('shift_template_id')->nullable()->constrained('shift_templates')->nullOnDelete()->index();
            $table->string('title', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by_type', 50)->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['provider_id', 'branch_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
