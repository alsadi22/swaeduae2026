<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_news_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('external_news_sources')->cascadeOnDelete();
            $table->string('external_guid', 512)->nullable();
            $table->text('external_url')->nullable();
            $table->text('original_title');
            $table->text('original_summary')->nullable();
            $table->string('original_image_url', 2048)->nullable();
            $table->timestamp('original_published_at')->nullable();
            $table->string('original_language', 16)->nullable();
            $table->text('normalized_title_en')->nullable();
            $table->text('normalized_title_ar')->nullable();
            $table->text('normalized_summary_en')->nullable();
            $table->text('normalized_summary_ar')->nullable();
            $table->string('local_feature_image', 2048)->nullable();
            $table->string('status', 32);
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_on_home')->default(false);
            $table->boolean('show_in_media_center')->default(true);
            $table->string('import_hash', 64)->unique();
            $table->timestamp('fetched_at');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['source_id', 'status']);
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_news_items');
    }
};
