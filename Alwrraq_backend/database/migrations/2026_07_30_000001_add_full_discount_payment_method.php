<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('apple_pay', 'google_pay', 'stc_pay', 'mada', 'visa', 'mastercard', 'amex', 'unionpay', 'card', 'full_discount') NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('apple_pay', 'google_pay', 'stc_pay', 'mada', 'visa', 'mastercard', 'amex', 'unionpay', 'card') NULL");
        }
    }
};
