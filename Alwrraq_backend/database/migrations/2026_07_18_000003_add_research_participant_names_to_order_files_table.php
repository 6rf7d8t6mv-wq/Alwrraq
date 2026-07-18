<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_files', function (Blueprint $table) {
            $table->string('research_student_name')->nullable()->after('research_title');
            $table->string('research_instructor_name')->nullable()->after('research_student_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_files', function (Blueprint $table) {
            $table->dropColumn(['research_student_name', 'research_instructor_name']);
        });
    }
};
