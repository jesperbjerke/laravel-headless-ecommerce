<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends BreadModel
{
    protected $fillable = [
        'name',
        'reference',
        'product_id',
        'value',
        'discount_value',
        'vat_value',
        'unit_value',
        'vat_percentage',
        'quantity'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.order'));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.product'));
    }

    public function getStock(): Stock
    {
        return Stock::where('product_id', $this->product_id)
                    ->where('store_id', $this->order->store_id)
                    ->firstOrFail();
    }

    public function reserveStock()
    {
        $stock = $this->getStock();
        $stock->outgoing_quantity += $this->quantity;
        $stock->current_quantity -= $this->quantity;
        $stock->save();
    }

    public function releaseReservedStock()
    {
        $stock = $this->getStock();
        $stock->outgoing_quantity -= $this->quantity;
        $stock->current_quantity += $this->quantity;
        $stock->save();
    }

    public function confirmReservedStock()
    {
        $stock = $this->getStock();
        $stock->outgoing_quantity -= $this->quantity;
        $stock->save();
    }

    public function returnStock($quantity = null)
    {
        $returnQuantity = $quantity ?: $this->quantity;
        $stock = $this->getStock();
        $stock->current_quantity += ($this->quantity >= $returnQuantity) ? $returnQuantity : $this->quantity;
        $stock->save();
    }
}
