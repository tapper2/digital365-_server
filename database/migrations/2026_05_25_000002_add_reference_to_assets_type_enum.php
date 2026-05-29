<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE landing_page_assets MODIFY COLUMN type ENUM('logo', 'image', 'reference') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE landing_page_assets MODIFY COLUMN type ENUM('logo', 'image') NOT NULL");
    }
};
