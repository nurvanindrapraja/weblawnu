<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->date('event_date')->nullable();
            $table->enum('status', ['draft', 'submitted', 'published', 'rejected'])->default('draft');
            $table->text('rejection_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('status');
            $table->index('published_at');
            $table->index('author_id');
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
