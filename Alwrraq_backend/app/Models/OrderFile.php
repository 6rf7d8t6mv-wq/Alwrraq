<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFile extends Model
{
    protected $fillable = [
        'order_id',
        'file_type',
        'original_name',
        'stored_name',
        'path',
        'size',
        'pages',
        'copies',
        'print_sides',
        'page_size',
        'paper_color',
        'thesis_project_type',
        'university_name',
        'cover_color',
        'writing_color',
        'cd_type',
        'cd_copies',
        'research_title',
        'research_student_name',
        'research_instructor_name',
        'binding_type',
        'print_price',
        'binding_price',
        'cd_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'print_price' => 'decimal:2',
            'binding_price' => 'decimal:2',
            'cd_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
