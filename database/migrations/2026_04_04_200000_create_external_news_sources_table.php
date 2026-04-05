<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 32);
            $table->text('endpoint_url')->nullable();
            $table->string('website_url', 2048)->nullable();
            $table->string('source_logo', 2048)->nullable();
            $table->string('label_en');
            $table->string('label_ar');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('fetch_interval_minutes')->default(360);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->json('parser_config')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_news_sources');
    }
};
