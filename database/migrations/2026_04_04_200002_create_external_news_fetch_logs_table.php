<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_news_fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('external_news_sources')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('status', 32);
            $table->unsignedInteger('items_found')->default(0);
            $table->unsignedInteger('items_created')->default(0);
            $table->unsignedInteger('items_updated')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_news_fetch_logs');
    }
};
