<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_files', function (Blueprint $table) {
            $table->string('image_print_type', 30)->nullable()->after('relative_path');
        });
    }

    public function down(): void
    {
        Schema::table('order_files', function (Blueprint $table) {
            $table->dropColumn('image_print_type');
        });
    }
};
