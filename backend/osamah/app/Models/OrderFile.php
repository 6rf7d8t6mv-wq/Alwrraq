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
        'thesis_project_type',
        'university_name',
        'cover_color',
        'writing_color',
        'research_title',
        'binding_type',
        'print_price',
        'binding_price',
        'total_price',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
