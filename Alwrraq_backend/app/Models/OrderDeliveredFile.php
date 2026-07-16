<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDeliveredFile extends Model
{
    protected $fillable = [
        'order_id',
        'original_name',
        'stored_name',
        'path',
        'mime',
        'size',
        'uploaded_by',
        'customer_downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'customer_downloaded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
