<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('announcements')) {
            return;
        }

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 26)->unique();
            $table->string('title_en');
            $table->string('title_ar');
            $table->text('message_en');
            $table->text('message_ar');
            $table->enum('target', ['all', 'users', 'providers', 'employees', 'branches'])->default('all')->index();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by_admin_id')->references('id')->on('admins')->onDelete('set null');

            $table->index(['active', 'ends_at']);
            $table->index(['target', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
