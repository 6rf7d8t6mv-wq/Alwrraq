<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_city')->nullable()->after('delivery_room');
            $table->string('delivery_district')->nullable()->after('delivery_city');
            $table->string('delivery_street')->nullable()->after('delivery_district');
            $table->string('delivery_map_url')->nullable()->after('delivery_street');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_city',
                'delivery_district',
                'delivery_street',
                'delivery_map_url',
            ]);
        });
    }
};
