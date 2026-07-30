<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_cancellation_audits', function (Blueprint $table): void {
            $table->id();
            $table->uuid('request_uuid')->unique();
            $table->string('external_event_id')->nullable()->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('moyasar_payment_id')->nullable()->index();
            $table->string('action', 30);
            $table->string('outcome', 30);
            $table->string('remote_status', 30)->nullable();
            $table->unsignedBigInteger('amount_minor')->nullable();
            $table->text('reason')->nullable();
            $table->string('error_code', 80)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'outcome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_cancellation_audits');
    }
};
