<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivered_file_original_name')->nullable()->after('admin_notes');
            $table->string('delivered_file_stored_name')->nullable()->after('delivered_file_original_name');
            $table->string('delivered_file_path')->nullable()->after('delivered_file_stored_name');
            $table->string('delivered_file_mime')->nullable()->after('delivered_file_path');
            $table->unsignedBigInteger('delivered_file_size')->nullable()->after('delivered_file_mime');
            $table->timestamp('delivered_file_uploaded_at')->nullable()->after('delivered_file_size');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivered_file_original_name',
                'delivered_file_stored_name',
                'delivered_file_path',
                'delivered_file_mime',
                'delivered_file_size',
                'delivered_file_uploaded_at',
            ]);
        });
    }
};
