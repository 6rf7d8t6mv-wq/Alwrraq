<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_delivered_files', function (Blueprint $table) {
            $table->timestamp('customer_downloaded_at')->nullable()->after('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::table('order_delivered_files', function (Blueprint $table) {
            $table->dropColumn('customer_downloaded_at');
        });
    }
};
