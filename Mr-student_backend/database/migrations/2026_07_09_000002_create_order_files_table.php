<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('file_type', ['word', 'pdf']);
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('pages')->default(1);
            $table->unsignedInteger('copies')->default(1);
            $table->enum('binding_type', ['tape', 'wire', 'normal', 'none'])->nullable();
            $table->unsignedInteger('print_price')->default(0);
            $table->unsignedInteger('binding_price')->default(0);
            $table->unsignedInteger('total_price')->default(0);
            $table->timestamps();

            $table->index(['file_type', 'binding_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_files');
    }
};
