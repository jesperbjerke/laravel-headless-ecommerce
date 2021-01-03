<?php

namespace Bjerke\Ecommerce\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryProduct extends Pivot
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.category'));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.product'));
    }
}
