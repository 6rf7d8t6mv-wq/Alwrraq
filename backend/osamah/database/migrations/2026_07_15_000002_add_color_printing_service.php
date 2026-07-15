<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY service_type ENUM('notes', 'books', 'color_printing', 'thesis', 'phd', 'formatting', 'research') NOT NULL");
        DB::statement("ALTER TABLE order_files MODIFY binding_type ENUM('tape', 'wire', 'normal', 'thermal', 'none') NULL");

        DB::statement('ALTER TABLE orders MODIFY print_total DECIMAL(10,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE orders MODIFY binding_total DECIMAL(10,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE orders MODIFY grand_total DECIMAL(10,2) NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE order_files MODIFY print_price DECIMAL(10,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE order_files MODIFY binding_price DECIMAL(10,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE order_files MODIFY total_price DECIMAL(10,2) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY service_type ENUM('notes', 'books', 'thesis', 'phd', 'formatting', 'research') NOT NULL");
        DB::statement("ALTER TABLE order_files MODIFY binding_type ENUM('tape', 'wire', 'normal', 'none') NULL");

        DB::statement('ALTER TABLE orders MODIFY print_total INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE orders MODIFY binding_total INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE orders MODIFY grand_total INT UNSIGNED NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE order_files MODIFY print_price INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE order_files MODIFY binding_price INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE order_files MODIFY total_price INT UNSIGNED NOT NULL DEFAULT 0');
    }
};
