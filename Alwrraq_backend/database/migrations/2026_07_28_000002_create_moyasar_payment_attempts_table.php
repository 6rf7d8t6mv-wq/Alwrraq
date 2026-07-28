<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moyasar_payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('moyasar_payment_id')->nullable()->unique();
            $table->json('order_ids');
            $table->json('order_amounts');
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3)->default('SAR');
            $table->string('status', 30)->default('pending');
            $table->string('payment_method', 30)->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('apple_pay', 'google_pay', 'stc_pay', 'mada', 'visa', 'mastercard', 'amex', 'unionpay', 'card') NULL");
            DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('apple_pay', 'google_pay', 'stc_pay', 'mada', 'visa', 'mastercard', 'amex', 'unionpay') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('apple_pay', 'google_pay', 'mada', 'visa', 'mastercard') NOT NULL");
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('apple_pay', 'google_pay', 'mada', 'visa', 'mastercard', 'card') NULL");
        }

        Schema::dropIfExists('moyasar_payment_attempts');
    }
};
