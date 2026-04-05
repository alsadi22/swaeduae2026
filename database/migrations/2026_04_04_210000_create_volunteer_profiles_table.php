<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->string('skills', 2000)->nullable();
            $table->string('availability', 500)->nullable();
            $table->string('emergency_contact_name', 255)->nullable();
            $table->string('emergency_contact_phone', 64)->nullable();
            $table->string('photo_path', 500)->nullable();
            $table->string('emirates_id_masked', 32)->nullable();
            $table->boolean('notification_email_opt_in')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_profiles');
    }
};
