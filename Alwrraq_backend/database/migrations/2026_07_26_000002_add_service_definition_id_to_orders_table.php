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
            $table->foreignId('service_definition_id')
                ->nullable()
                ->after('service_type')
                ->constrained('service_definitions')
                ->nullOnDelete();
        });

        DB::table('service_definitions')->orderBy('id')->each(function ($service) {
            DB::table('orders')
                ->where('service_type', $service->code)
                ->whereNull('service_definition_id')
                ->update(['service_definition_id' => $service->id]);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_definition_id');
        });
    }
};
