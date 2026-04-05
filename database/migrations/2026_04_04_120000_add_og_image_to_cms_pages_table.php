<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->string('og_image', 2048)->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn('og_image');
        });
    }
};
