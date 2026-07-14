<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('discount_code')->nullable()->after('binding_total');
            $table->unsignedInteger('discount_amount')->default(0)->after('discount_code');
            $table->foreignId('discount_applied_by')->nullable()->after('discount_amount')->constrained('users')->nullOnDelete();
            $table->timestamp('discount_applied_at')->nullable()->after('discount_applied_by');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_applied_by');
            $table->dropColumn(['discount_code', 'discount_amount', 'discount_applied_at']);
        });
    }
};
