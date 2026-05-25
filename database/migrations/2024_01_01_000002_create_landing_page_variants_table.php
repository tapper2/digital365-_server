<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('variant_index');
            $table->longText('html_content');
            $table->string('thumbnail_path')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('landing_pages', function (Blueprint $table) {
            $table->foreign('selected_variant_id')
                ->references('id')->on('landing_page_variants')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropForeign(['selected_variant_id']);
        });
        Schema::dropIfExists('landing_page_variants');
    }
};
