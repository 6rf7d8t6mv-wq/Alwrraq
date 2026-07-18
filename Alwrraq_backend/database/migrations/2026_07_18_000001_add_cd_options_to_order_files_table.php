<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_files', function (Blueprint $table) {
            $table->string('cd_type')->default('none')->after('writing_color');
            $table->unsignedInteger('cd_copies')->default(0)->after('cd_type');
            $table->decimal('cd_price', 10, 2)->default(0)->after('binding_price');
        });
    }

    public function down(): void
    {
        Schema::table('order_files', function (Blueprint $table) {
            $table->dropColumn(['cd_type', 'cd_copies', 'cd_price']);
        });
    }
};
