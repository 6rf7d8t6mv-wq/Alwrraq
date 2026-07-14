<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'service_type',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'print_total',
        'binding_total',
        'discount_code',
        'discount_amount',
        'discount_applied_by',
        'discount_applied_at',
        'delivery_method',
        'delivery_fee',
        'delivery_unit',
        'delivery_floor',
        'delivery_room',
        'delivery_city',
        'delivery_district',
        'delivery_street',
        'delivery_map_url',
        'grand_total',
        'customer_notes',
        'admin_notes',
        'admin_opened_at',
        'admin_notification_seen_at',
        'customer_notification_seen_at',
        'delivered_file_original_name',
        'delivered_file_stored_name',
        'delivered_file_path',
        'delivered_file_mime',
        'delivered_file_size',
        'delivered_file_uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'admin_opened_at' => 'datetime',
            'admin_notification_seen_at' => 'datetime',
            'customer_notification_seen_at' => 'datetime',
            'discount_applied_at' => 'datetime',
            'delivered_file_uploaded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(OrderFile::class);
    }

    public function deliveredFiles(): HasMany
    {
        return $this->hasMany(OrderDeliveredFile::class)->latest();
    }

    public function discountApplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'discount_applied_by');
    }

    public function baseTotal(): int
    {
        return (int) $this->print_total + (int) $this->binding_total;
    }

    public function subtotalAfterDiscount(): int
    {
        return max(0, $this->baseTotal() - (int) $this->discount_amount);
    }
}
