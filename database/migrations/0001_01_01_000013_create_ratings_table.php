<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('visitor_hash', 100);
            $table->string('session_hash', 100)->nullable();
            $table->timestamps();

            $table->index('post_id');
            $table->index('visitor_hash');
            $table->index(['post_id', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
