<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->enum('status', ['draft', 'submitted', 'review', 'approved', 'published', 'rejected', 'archived'])->default('draft');
            $table->text('rejection_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('views')->default(0);
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->unsignedBigInteger('rating_count')->default(0);
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('status');
            $table->index('published_at');
            $table->index('author_id');
            $table->index('category_id');
            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
