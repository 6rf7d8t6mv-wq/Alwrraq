<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceDefinition extends Model
{
    public const WORKFLOWS = [
        'notes' => 'طباعة مذكرات وPDF',
        'color_printing' => 'طباعة ملفات بالألوان',
        'books' => 'طباعة وتجليد كتب',
        'thesis' => 'رسالة ماجستير وبحث تخرج',
        'phd' => 'رسالة دكتوراه',
        'formatting' => 'تنسيق وتدقيق ملف Word',
        'research' => 'إنشاء بحث بدون رفع ملف',
        'stationery' => 'متجر القرطاسية',
    ];

    protected $fillable = [
        'code',
        'title',
        'description',
        'icon',
        'workflow_type',
        'requires_file',
        'is_active',
        'is_system',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'requires_file' => 'boolean',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
