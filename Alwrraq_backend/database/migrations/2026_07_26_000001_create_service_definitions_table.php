<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('icon', 20)->nullable();
            $table->string('workflow_type', 40);
            $table->boolean('requires_file')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('service_definitions')->insert([
            [
                'code' => 'notes',
                'title' => 'طباعة المذكرات وملفات ال PDF',
                'description' => 'طباعة أبيض وأسود بدون ألوان للمذكرات وملفات ال PDF بجميع أحجامها وتغليفها.',
                'icon' => '📝',
                'workflow_type' => 'notes',
                'requires_file' => true,
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'color_printing',
                'title' => 'طباعة الملفات بالألوان',
                'description' => 'طباعة ملفات PDF ملونة مع اختيار حجم الصفحة وعدد النسخ والتغليف.',
                'icon' => '🎨',
                'workflow_type' => 'color_printing',
                'requires_file' => true,
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'books',
                'title' => 'طباعة وتجليد كتب كعب جلد طبيعي',
                'description' => 'طباعة ملفات PDF والكتب بجميع أحجامها والتغليف وتجليد كعب جلد طبيعي.',
                'icon' => '📘',
                'workflow_type' => 'books',
                'requires_file' => true,
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'thesis',
                'title' => 'طباعة وتجليد رسالة ماجستير أو بحث تكميلي أو بحث تخرج',
                'description' => 'خدمة مخصصة للرسائل العلمية والبحث التكميلي وبحث التخرج مع احتساب النسخ والتجليد.',
                'icon' => '📚',
                'workflow_type' => 'thesis',
                'requires_file' => true,
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'phd',
                'title' => 'طباعة وتجليد رسالة دكتوراه',
                'description' => 'تجهيز ملفات الدكتوراه للطباعة والتجليد مع عرض كامل للتكاليف قبل الإضافة للسلة.',
                'icon' => '🎓',
                'workflow_type' => 'phd',
                'requires_file' => true,
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'formatting',
                'title' => 'تنسيق وتدقيق الرسائل الجامعية',
                'description' => 'رفع ملف Word فقط واحتساب سعر التنسيق تلقائيًا حسب عدد الصفحات.',
                'icon' => '✍️',
                'workflow_type' => 'formatting',
                'requires_file' => true,
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 60,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'research',
                'title' => 'إنشاء بحوث جامعية وأكاديمية ودراسية',
                'description' => 'اكتب اسم البحث وعدد الصفحات المطلوبة، ويتم احتساب سعر الخدمة تلقائيًا.',
                'icon' => '📑',
                'workflow_type' => 'research',
                'requires_file' => false,
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 70,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'stationery',
                'title' => 'القرطاسية',
                'description' => 'تصفح منتجات القرطاسية وابحث عنها وأضف ما تحتاجه إلى السلة.',
                'icon' => '✏️',
                'workflow_type' => 'stationery',
                'requires_file' => false,
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 80,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('service_definitions');
    }
};
