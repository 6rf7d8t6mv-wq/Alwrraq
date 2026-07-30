<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeDraft extends Model
{
    public const TEMPLATES = [
        'executive_classic' => 'التنفيذي الفاخر',
        'royal_gold' => 'الملكي الذهبي',
        'midnight_luxury' => 'فخامة منتصف الليل',
        'emerald_signature' => 'التوقيع الزمردي',
        'modern_silk' => 'الحرير العصري',
    ];

    public const DEFAULT_SECTION_ORDER = [
        'education',
        'experience',
        'skills',
        'languages',
        'certificates',
        'projects',
        'achievements',
        'volunteering',
        'references',
    ];

    protected $fillable = [
        'user_id',
        'order_id',
        'template_id',
        'language',
        'content',
        'section_order',
        'hidden_sections',
        'photo_path',
        'status',
        'pdf_path',
        'image_path',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'section_order' => 'array',
            'hidden_sections' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPaid(): bool
    {
        return $this->order
            && in_array($this->order->payment_status, ['paid', 'voided', 'refunded'], true);
    }

    public function templateName(): string
    {
        return self::TEMPLATES[$this->template_id] ?? self::TEMPLATES['executive_classic'];
    }
}
