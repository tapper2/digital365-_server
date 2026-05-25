<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_page_variants', function (Blueprint $table) {
            $table->string('image_url', 1000)->nullable()->after('html_content');
        });

        // Make html_content nullable via raw SQL (avoids doctrine/dbal requirement)
        \DB::statement('ALTER TABLE landing_page_variants MODIFY html_content LONGTEXT NULL');
    }

    public function down(): void
    {
        Schema::table('landing_page_variants', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
        \DB::statement('ALTER TABLE landing_page_variants MODIFY html_content LONGTEXT NOT NULL');
    }
};
