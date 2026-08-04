<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('service_definitions')
            ->where('code', 'books')
            ->whereIn('title', [
                'طباعة وتجليد كتب كعب جلد طبيعي',
                'طباعة وتجليد كتب',
            ])
            ->update([
                'title' => 'تصوير وتجليد الكتب كعب جلد طبيعي',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('service_definitions')
            ->where('code', 'books')
            ->where('title', 'تصوير وتجليد الكتب كعب جلد طبيعي')
            ->update([
                'title' => 'طباعة وتجليد كتب كعب جلد طبيعي',
                'updated_at' => now(),
            ]);
    }
};
