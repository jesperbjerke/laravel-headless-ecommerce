<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Payment extends BreadModel
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'currency',
        'value',
        'status',
        'order_id',
        'reference'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public static function boot(): void
    {
        parent::boot();
        static::creating(static function (Order $order) {
            $order->setAttribute($order->getKeyName(), Str::uuid());
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.order'));
    }

    public function paymentLogs(): HasMany
    {
        return $this->hasMany(config('ecommerce.models.payment_log'));
    }
}
