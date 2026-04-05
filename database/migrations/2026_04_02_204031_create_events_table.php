<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('title_en');
            $table->string('title_ar')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('geofence_radius_meters')->default(100);
            $table->boolean('geofence_strict')->default(true);
            $table->unsignedInteger('min_gps_accuracy_meters')->nullable()->default(75);
            $table->dateTime('checkin_window_starts_at');
            $table->dateTime('checkin_window_ends_at');
            $table->dateTime('event_starts_at');
            $table->dateTime('event_ends_at');
            $table->unsignedSmallInteger('checkout_grace_minutes_after_event')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
