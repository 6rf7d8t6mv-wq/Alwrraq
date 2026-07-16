<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_delivered_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('orders')
            ->whereNotNull('delivered_file_path')
            ->orderBy('id')
            ->get()
            ->each(function ($order) {
                DB::table('order_delivered_files')->insert([
                    'order_id' => $order->id,
                    'original_name' => $order->delivered_file_original_name ?: 'delivered-file',
                    'stored_name' => $order->delivered_file_stored_name ?: basename($order->delivered_file_path),
                    'path' => $order->delivered_file_path,
                    'mime' => $order->delivered_file_mime,
                    'size' => $order->delivered_file_size ?: 0,
                    'uploaded_by' => null,
                    'created_at' => $order->delivered_file_uploaded_at ?: now(),
                    'updated_at' => $order->delivered_file_uploaded_at ?: now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_delivered_files');
    }
};
