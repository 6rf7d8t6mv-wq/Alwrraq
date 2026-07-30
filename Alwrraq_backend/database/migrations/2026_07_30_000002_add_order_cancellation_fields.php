<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('refund_method', 20)->nullable()->after('paid_at');
            $table->decimal('refunded_amount', 10, 2)->default(0)->after('refund_method');
            $table->foreignId('cancelled_by')->nullable()->after('refunded_amount')->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            $table->text('cancel_reason')->nullable()->after('cancelled_at');
            $table->timestamp('refunded_at')->nullable()->after('cancel_reason');
            $table->timestamp('voided_at')->nullable()->after('refunded_at');
            $table->index('payment_reference');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('unpaid', 'paid', 'voided', 'refunded') NOT NULL DEFAULT 'unpaid'");
            DB::statement("ALTER TABLE payments MODIFY payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'voided') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid'");
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['payment_reference']);
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn([
                'refund_method',
                'refunded_amount',
                'cancelled_at',
                'cancel_reason',
                'refunded_at',
                'voided_at',
            ]);
        });
    }
};
