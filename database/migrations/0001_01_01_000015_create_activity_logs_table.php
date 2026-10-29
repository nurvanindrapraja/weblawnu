<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('module', 100);
            $table->string('action', 100);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_hash', 100)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('module');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
