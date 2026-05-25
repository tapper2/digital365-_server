<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_styles', function (Blueprint $table) {
            $table->id();
            $table->string('name_he');
            $table->string('name_en');
            $table->string('tags_he')->nullable();
            $table->string('suitable_for_he')->nullable();
            $table->text('image_prompt');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_styles');
    }
};
