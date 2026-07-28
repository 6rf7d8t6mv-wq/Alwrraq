<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoyasarPaymentAttempt extends Model
{
    protected $fillable = [
        'reference',
        'user_id',
        'moyasar_payment_id',
        'order_ids',
        'order_amounts',
        'amount_minor',
        'currency',
        'status',
        'payment_method',
        'failure_message',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'order_ids' => 'array',
            'order_amounts' => 'array',
            'amount_minor' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }
}
