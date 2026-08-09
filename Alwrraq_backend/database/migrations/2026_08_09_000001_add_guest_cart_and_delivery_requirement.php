<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->uuid('guest_token')->nullable()->after('user_id')->index();
        });

        Schema::table('service_definitions', function (Blueprint $table) {
            $table->boolean('requires_delivery')->default(false)->after('requires_file');
        });

        DB::table('service_definitions')
            ->whereIn('workflow_type', ['notes', 'books', 'color_printing', 'thesis', 'phd', 'stationery'])
            ->update(['requires_delivery' => true]);
    }

    public function down(): void
    {
        Schema::table('service_definitions', function (Blueprint $table) {
            $table->dropColumn('requires_delivery');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['guest_token']);
            $table->dropColumn('guest_token');
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
