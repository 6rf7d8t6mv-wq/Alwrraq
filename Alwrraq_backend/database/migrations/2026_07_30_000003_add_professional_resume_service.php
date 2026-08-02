<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY service_type ENUM('notes', 'books', 'color_printing', 'thesis', 'phd', 'formatting', 'research', 'stationery', 'images', 'resume') NOT NULL");
        }

        Schema::create('resume_drafts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('template_id', 50)->default('executive_classic');
            $table->string('language', 20)->default('bilingual');
            $table->json('content')->nullable();
            $table->json('section_order')->nullable();
            $table->json('hidden_sections')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status', 30)->default('draft');
            $table->string('pdf_path')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        $now = now();
        $serviceId = DB::table('service_definitions')->insertGetId([
            'code' => 'resume',
            'title' => 'إنشاء سيرة ذاتية احترافية',
            'description' => 'أنشئ سيرتك الذاتية بتصميم فاخر واحترافي، أدخل بياناتك وشاهد النتيجة مباشرة، ثم ادفع وحمّل سيرتك الذاتية بصيغة PDF أو صورة عالية الجودة.',
            'icon' => '📄',
            'workflow_type' => 'resume',
            'requires_file' => false,
            'is_active' => true,
            'is_system' => false,
            'sort_order' => 85,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('service_price_settings')->updateOrInsert(
            ['key' => 'service_definition_'.$serviceId.'_price'],
            ['value' => 5, 'updated_by' => null, 'created_at' => $now, 'updated_at' => $now]
        );
    }

    public function down(): void
    {
        $serviceId = DB::table('service_definitions')->where('code', 'resume')->value('id');
        if ($serviceId) {
            DB::table('service_price_settings')->where('key', 'service_definition_'.$serviceId.'_price')->delete();
            DB::table('service_definitions')->where('id', $serviceId)->delete();
        }

        Schema::dropIfExists('resume_drafts');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY service_type ENUM('notes', 'books', 'color_printing', 'thesis', 'phd', 'formatting', 'research', 'stationery', 'images') NOT NULL");
        }
    }
};
