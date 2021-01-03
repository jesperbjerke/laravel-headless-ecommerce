<?php

namespace Bjerke\Ecommerce\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductStore extends Pivot
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.product'));
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.store'));
    }
}
