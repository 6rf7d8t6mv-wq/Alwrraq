<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE order_files MODIFY file_type ENUM('word', 'pdf', 'research') NOT NULL");

        Schema::table('order_files', function (Blueprint $table) {
            $table->string('research_title')->nullable()->after('university_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_files', function (Blueprint $table) {
            $table->dropColumn('research_title');
        });

        DB::statement("ALTER TABLE order_files MODIFY file_type ENUM('word', 'pdf') NOT NULL");
    }
};
