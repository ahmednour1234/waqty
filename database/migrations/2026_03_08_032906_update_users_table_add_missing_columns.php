<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'uuid')) {
                $table->string('uuid', 26)->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'date_birth')) {
                $table->date('date_birth')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female'])->nullable()->after('date_birth');
            }
            if (!Schema::hasColumn('users', 'image_path')) {
                $table->string('image_path')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'active')) {
                $table->boolean('active')->default(true)->after('password');
            }
            if (!Schema::hasColumn('users', 'blocked')) {
                $table->boolean('blocked')->default(false)->after('active');
            }
            if (!Schema::hasColumn('users', 'banned')) {
                $table->boolean('banned')->default(false)->after('blocked');
            }
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('banned');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        if (Schema::hasColumn('users', 'uuid')) {
            DB::table('users')->whereNull('uuid')->get()->each(function ($user) {
                DB::table('users')->where('id', $user->id)->update(['uuid' => (string) Str::ulid()]);
            });

            Schema::table('users', function (Blueprint $table) {
                $table->string('uuid', 26)->nullable(false)->unique()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['uuid', 'phone', 'date_birth', 'gender', 'image_path', 'active', 'blocked', 'banned', 'email_verified_at', 'last_login_at', 'deleted_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    if ($column === 'phone') {
                        $table->dropUnique(['phone']);
                    }
                    if ($column === 'uuid') {
                        $table->dropUnique(['uuid']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
