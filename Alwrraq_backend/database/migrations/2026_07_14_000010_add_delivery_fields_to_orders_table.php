<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_method')->nullable()->after('discount_applied_at');
            $table->unsignedInteger('delivery_fee')->default(0)->after('delivery_method');
            $table->string('delivery_unit')->nullable()->after('delivery_fee');
            $table->string('delivery_floor')->nullable()->after('delivery_unit');
            $table->string('delivery_room')->nullable()->after('delivery_floor');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_method',
                'delivery_fee',
                'delivery_unit',
                'delivery_floor',
                'delivery_room',
            ]);
        });
    }
};
