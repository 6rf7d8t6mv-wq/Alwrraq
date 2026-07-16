<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE order_files MODIFY print_sides VARCHAR(255) NOT NULL DEFAULT 'two_sides'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE order_files MODIFY print_sides VARCHAR(255) NOT NULL DEFAULT 'one_side'");
    }
};
