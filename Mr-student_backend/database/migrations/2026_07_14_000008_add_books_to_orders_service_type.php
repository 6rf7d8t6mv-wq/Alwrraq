<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY service_type ENUM('notes', 'books', 'thesis', 'phd', 'formatting', 'research') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY service_type ENUM('notes', 'thesis', 'phd', 'formatting', 'research') NOT NULL");
    }
};
