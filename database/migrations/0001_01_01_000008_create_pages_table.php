<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
