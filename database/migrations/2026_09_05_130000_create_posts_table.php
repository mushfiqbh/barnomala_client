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
            $table->string('type', 64);
            $table->string('source_type', 32);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->string('class_label')->nullable();
            $table->date('published_at')->nullable();
            $table->json('image_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_urgent')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['type', 'source_type', 'is_active', 'published_at']);
            $table->index(['type', 'source_type', 'is_active', 'sort_order']);
            $table->unique(['source_type', 'source_id']);
            $table->index(['source_type', 'legacy_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
