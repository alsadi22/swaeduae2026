<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->boolean('show_on_programs')->default(false)->after('show_on_home');
            $table->index('show_on_programs');
        });
    }

    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropIndex(['show_on_programs']);
            $table->dropColumn('show_on_programs');
        });
    }
};
