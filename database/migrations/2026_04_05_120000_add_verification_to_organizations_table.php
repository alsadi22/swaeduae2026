<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('verification_status', 32)->default('approved')->after('name_ar');
            $table->text('verification_review_note')->nullable()->after('verification_status');
            $table->timestamp('verification_reviewed_at')->nullable()->after('verification_review_note');
            $table->foreignId('registered_by_user_id')->nullable()->after('verification_reviewed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registered_by_user_id');
            $table->dropColumn([
                'verification_status',
                'verification_review_note',
                'verification_reviewed_at',
            ]);
        });
    }
};
