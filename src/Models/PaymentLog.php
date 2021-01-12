<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends BreadModel
{
    protected $fillable = [
        'payment_id',
        'type',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.payment'));
    }
}
