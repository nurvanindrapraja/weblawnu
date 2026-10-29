<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('role', 20); // reporter | redaktur
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending'); // pending | approved | rejected
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
