<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends BreadModel
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.order'));
    }
}
