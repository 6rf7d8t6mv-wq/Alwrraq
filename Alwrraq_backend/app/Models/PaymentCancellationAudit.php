<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentCancellationAudit extends Model
{
    protected $fillable = [
        'request_uuid',
        'external_event_id',
        'order_id',
        'user_id',
        'moyasar_payment_id',
        'action',
        'outcome',
        'remote_status',
        'amount_minor',
        'reason',
        'error_code',
    ];

    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
