<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLog extends BreadModel
{
    protected $fillable = [
        'order_id',
        'type',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.order'));
    }
}
