<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->text('summary')->nullable();
            $table->string('url')->unique();
            $table->string('external_id')->nullable();
            $table->datetime('published_at');
            $table->string('author')->nullable();
            $table->string('category')->nullable();
            $table->string('image_url')->nullable();
            $table->foreignId('source_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['published_at', 'category']);
            $table->index(['author', 'published_at']);
            $table->fullText(['title', 'content', 'summary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
