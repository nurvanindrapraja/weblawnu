<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('filename', 255);
            $table->string('original_name', 255);
            $table->string('disk', 50)->default('public');
            $table->string('path', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->text('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
