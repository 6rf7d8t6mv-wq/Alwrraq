<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY service_type ENUM('notes', 'books', 'color_printing', 'thesis', 'phd', 'formatting', 'research', 'stationery', 'images') NOT NULL");
        DB::statement("ALTER TABLE order_files MODIFY file_type ENUM('word', 'pdf', 'research', 'image') NOT NULL");

        Schema::table('order_files', function (Blueprint $table) {
            $table->string('relative_path', 1000)->nullable()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('order_files', function (Blueprint $table) {
            $table->dropColumn('relative_path');
        });

        DB::statement("ALTER TABLE order_files MODIFY file_type ENUM('word', 'pdf', 'research') NOT NULL");
        DB::statement("ALTER TABLE orders MODIFY service_type ENUM('notes', 'books', 'color_printing', 'thesis', 'phd', 'formatting', 'research', 'stationery') NOT NULL");
    }
};
