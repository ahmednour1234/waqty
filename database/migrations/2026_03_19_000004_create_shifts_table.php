<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('shifts')) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->unsignedBigInteger('provider_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('shift_template_id')->nullable()->index();
            $table->string('title', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by_type', 50)->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('provider_id',       'shifts_provider_id_fk')       ->references('id')->on('providers')        ->cascadeOnDelete();
            $table->foreign('branch_id',          'shifts_branch_id_fk')         ->references('id')->on('provider_branches')->nullOnDelete();
            $table->foreign('shift_template_id',  'shifts_shift_template_id_fk') ->references('id')->on('shift_templates')  ->nullOnDelete();

            $table->index(['provider_id', 'branch_id', 'active']);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
