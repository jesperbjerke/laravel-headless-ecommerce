<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Bjerke\Ecommerce\Enums\StockLogTrigger;
use Bjerke\Ecommerce\Enums\StockLogType;
use Bjerke\Ecommerce\Exceptions\InvalidStockQuantity;
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

    public function getStockAttribute(): Stock
    {
        return Stock::where('product_id', $this->product_id)
                    ->where('store_id', $this->order->store_id)
                    ->firstOrFail();
    }

    public function reserveStock(): void
    {
        $stock = $this->stock;

        // Validate stock is not already reserved
        $reservedSum = StockLog::where('stock_id', $stock->id)
                             ->where('type', StockLogType::RESERVED)
                             ->where('reference->order_id', $this->order_id)
                             ->where('reference->order_item_id', $this->id)
                             ->sum('quantity');

        $quantityToReserve = ($this->quantity - $reservedSum);

        if (
            $reservedSum >= $this->quantity ||
            $stock->available_quantity < $quantityToReserve
        ) {
            throw new InvalidStockQuantity();
        }

        $stock->outgoing_quantity += $quantityToReserve;
        $stock->current_quantity -= $quantityToReserve;
        $stock->save();

        StockLog::create([
             'type' => StockLogType::RESERVED,
             'trigger' => StockLogTrigger::ORDER_ITEM,
             'stock_id' => $stock->id,
             'reference' => [
                 'order_id' => $this->order_id,
                 'order_item_id' => $this->id
             ],
             'quantity' => $quantityToReserve
         ]);
    }

    public function releaseReservedStock(): void
    {
        $stock = $this->stock;

        // Validate stock can be released
        $stockLogs = StockLog::where('stock_id', $stock->id)
                             ->where('reference->order_id', $this->order_id)
                             ->where('reference->order_item_id', $this->id)
                             ->get();

        $reservedSum = $stockLogs->where('type', StockLogType::RESERVED)
                                 ->sum('quantity');

        $unconfirmedQuantity = $reservedSum - $stockLogs->where('type', [
            StockLogType::RELEASED,
            StockLogType::CONFIRMED,
            StockLogType::RETURNED,
        ])->sum('quantity');

        if (
            $unconfirmedQuantity !== $this->quantity ||
            $stock->outgoing_quantity < $this->quantity
        ) {
            throw new InvalidStockQuantity();
        }

        $stock->outgoing_quantity -= $this->quantity;
        $stock->current_quantity += $this->quantity;
        $stock->save();

        StockLog::create([
            'type' => StockLogType::RELEASED,
            'trigger' => StockLogTrigger::ORDER_ITEM,
            'stock_id' => $stock->id,
            'reference' => [
                'order_id' => $this->order_id,
                'order_item_id' => $this->id
            ],
            'quantity' => $this->quantity
        ]);
    }

    public function confirmReservedStock(): void
    {
        $stock = $this->stock;

        // Validate stock can be confirmed
        $stockLogs = StockLog::where('stock_id', $stock->id)
                             ->where('reference->order_id', $this->order_id)
                             ->where('reference->order_item_id', $this->id)
                             ->get();

        $reservedSum = $stockLogs->where('type', StockLogType::RESERVED)
                                 ->sum('quantity');

        $unconfirmedQuantity = $reservedSum - $stockLogs->where('type', [
            StockLogType::RELEASED,
            StockLogType::CONFIRMED,
            StockLogType::RETURNED,
        ])->sum('quantity');

        if (
            $unconfirmedQuantity !== $this->quantity ||
            $stock->outgoing_quantity < $this->quantity
        ) {
            throw new InvalidStockQuantity();
        }

        $stock->outgoing_quantity -= $this->quantity;
        $stock->save();

        StockLog::create([
            'type' => StockLogType::CONFIRMED,
            'trigger' => StockLogTrigger::ORDER_ITEM,
            'stock_id' => $stock->id,
            'reference' => [
                'order_id' => $this->order_id,
                'order_item_id' => $this->id
            ],
            'quantity' => $this->quantity
        ]);
    }

    public function returnStock($quantity = null): void
    {
        $returnQuantity = $quantity ?: $this->quantity;
        $stock = $this->stock;

        // Validate stock can be returned
        $stockLogs = StockLog::where('stock_id', $stock->id)
                             ->where('type', [
                                 StockLogType::CONFIRMED,
                                 StockLogType::RETURNED
                             ])
                             ->where('reference->order_id', $this->order_id)
                             ->where('reference->order_item_id', $this->id)
                             ->get();

        $confirmedSum = $stockLogs->where('type', StockLogType::CONFIRMED)
                                  ->sum('quantity');
        $returnedSum = $stockLogs->where('type', StockLogType::RETURNED)
                                 ->sum('quantity');

        $remainingQuantity = $confirmedSum - $returnedSum;

        if ($remainingQuantity < $returnQuantity) {
            throw new InvalidStockQuantity();
        }

        // Update stock
        $stock->current_quantity += ($this->quantity >= $returnQuantity) ? $returnQuantity : $this->quantity;
        $stock->save();

        StockLog::create([
            'type' => StockLogType::RETURNED,
            'trigger' => StockLogTrigger::ORDER_ITEM,
            'stock_id' => $stock->id,
            'reference' => [
                'order_id' => $this->order_id,
                'order_item_id' => $this->id
            ],
            'quantity' => $returnQuantity
        ]);
    }
}
