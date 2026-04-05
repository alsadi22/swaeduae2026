<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('locale', 8);
            $table->string('title');
            $table->string('meta_description')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('status', 32)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['slug', 'locale']);
            $table->index(['status', 'locale', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pages');
    }
};
