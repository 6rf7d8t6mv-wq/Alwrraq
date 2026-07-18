<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY service_type ENUM('notes', 'books', 'color_printing', 'thesis', 'phd', 'formatting', 'research', 'stationery') NOT NULL");

        Schema::create('stationery_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name');
            $table->string('product_type');
            $table->decimal('price', 10, 2);
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('order_product_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stationery_product_id')->nullable()->constrained('stationery_products')->nullOnDelete();
            $table->string('product_name');
            $table->string('company_name');
            $table->string('product_type');
            $table->string('image_path')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();

            $table->unique(['order_id', 'stationery_product_id'], 'order_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_product_items');
        Schema::dropIfExists('stationery_products');

        DB::statement("ALTER TABLE orders MODIFY service_type ENUM('notes', 'books', 'color_printing', 'thesis', 'phd', 'formatting', 'research') NOT NULL");
    }
};
