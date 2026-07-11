<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_files', function (Blueprint $table) {
            $table
                ->enum('thesis_project_type', ['thesis', 'supplementary', 'graduation'])
                ->nullable()
                ->after('copies');
        });
    }

    public function down(): void
    {
        Schema::table('order_files', function (Blueprint $table) {
            $table->dropColumn('thesis_project_type');
        });
    }
};
