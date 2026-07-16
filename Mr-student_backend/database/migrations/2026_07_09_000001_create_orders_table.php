<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('service_type', ['notes', 'thesis', 'phd']);
            $table->enum('status', ['new', 'reviewing', 'priced', 'processing', 'completed', 'cancelled'])->default('new');
            $table->unsignedInteger('print_total')->default(0);
            $table->unsignedInteger('binding_total')->default(0);
            $table->unsignedInteger('grand_total')->default(0);
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['service_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
