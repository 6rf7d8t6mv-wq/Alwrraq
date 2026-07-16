<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('apple_pay', 'google_pay', 'mada', 'visa', 'mastercard', 'card') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('apple_pay', 'card') NULL");
    }
};
