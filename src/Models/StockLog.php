<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends BreadModel
{
    protected $fillable = [
        'type',
        'trigger',
        'stock_id',
        'quantity',
        'reference'
    ];

    protected $casts = [
        'reference' => 'array'
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.stock'));
    }
}
