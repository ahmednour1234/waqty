<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = 'provider_password_resets';
        $database = DB::getDatabaseName();
        
        $tableExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.tables 
            WHERE table_schema = ? 
            AND table_name = ?
        ", [$database, $tableName]);
        
        if ($tableExists[0]->count > 0) {
            return;
        }

        try {
            Schema::create('provider_password_resets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade')->index();
                $table->string('token_hash', 255);
                $table->timestamp('expires_at')->index();
                $table->timestamp('used_at')->nullable();
                $table->string('created_ip', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('created_at')->nullable();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            if ($errorCode == 1005 || 
                str_contains($errorMessage, 'already exists') || 
                str_contains($errorMessage, 'Duplicate key') ||
                str_contains($errorMessage, 'errno: 121')) {
                return;
            }
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_password_resets');
    }
};
