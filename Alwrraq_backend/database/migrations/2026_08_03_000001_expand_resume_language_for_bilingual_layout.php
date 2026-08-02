<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE resume_drafts MODIFY language VARCHAR(20) NOT NULL DEFAULT 'bilingual'");
        }

        DB::table('resume_drafts')->update(['language' => 'bilingual']);
    }

    public function down(): void
    {
        DB::table('resume_drafts')->where('language', 'bilingual')->update(['language' => 'ar']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE resume_drafts MODIFY language VARCHAR(2) NOT NULL DEFAULT 'ar'");
        }
    }
};
