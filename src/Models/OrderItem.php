<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Bjerke\Ecommerce\Enums\OrderLogType;
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

    public function reserveStock()
    {
        $stock = $this->stock;

        // Validate stock can be released
        $orderLogs = OrderLog::where('order_id', $this->order_id)
                             ->where('type', [
                                 OrderLogType::STOCK_RESERVED,
                                 OrderLogType::STOCK_RELEASED,
                                 OrderLogType::STOCK_CONFIRMED,
                                 OrderLogType::STOCK_RETURNED
                             ])
                             ->where('meta->order_item_id', $this->id)
                             ->where('meta->stock_id', $stock->id)
                             ->get();

        $reservedSum = $orderLogs->where('type', OrderLogType::STOCK_RESERVED)
                                 ->sum(fn (OrderLog $log) => $log->meta['quantity']);

        $unconfirmedQuantity = $reservedSum - $orderLogs->where('type', [
            OrderLogType::STOCK_RELEASED,
            OrderLogType::STOCK_CONFIRMED,
            OrderLogType::STOCK_RETURNED,
        ])->sum(fn (OrderLog $log) => $log->meta['quantity']);

        if (
            $unconfirmedQuantity !== $this->quantity ||
            $stock->available_quantity < $this->quantity
        ) {
            throw new InvalidStockQuantity();
        }

        $stock->outgoing_quantity += $this->quantity;
        $stock->current_quantity -= $this->quantity;
        $stock->save();

        OrderLog::create([
            'order_id' => $this->order_id,
            'type' => OrderLogType::STOCK_RESERVED,
            'meta' => [
                'stock_id' => $stock->id,
                'order_item_id' => $this->id,
                'quantity' => $this->quantity
            ]
        ]);
    }

    public function releaseReservedStock()
    {
        $stock = $this->stock;

        // Validate stock can be released
        $orderLogs = OrderLog::where('order_id', $this->order_id)
                             ->where('type', [
                                 OrderLogType::STOCK_RESERVED,
                                 OrderLogType::STOCK_RELEASED,
                                 OrderLogType::STOCK_CONFIRMED,
                                 OrderLogType::STOCK_RETURNED
                             ])
                             ->where('meta->order_item_id', $this->id)
                             ->where('meta->stock_id', $stock->id)
                             ->get();

        $reservedSum = $orderLogs->where('type', OrderLogType::STOCK_RESERVED)
                                 ->sum(fn (OrderLog $log) => $log->meta['quantity']);

        $unconfirmedQuantity = $reservedSum - $orderLogs->where('type', [
            OrderLogType::STOCK_RELEASED,
            OrderLogType::STOCK_CONFIRMED,
            OrderLogType::STOCK_RETURNED,
        ])->sum(fn (OrderLog $log) => $log->meta['quantity']);

        if (
            $unconfirmedQuantity !== $this->quantity ||
            $stock->outgoing_quantity < $this->quantity
        ) {
            throw new InvalidStockQuantity();
        }

        $stock->outgoing_quantity -= $this->quantity;
        $stock->current_quantity += $this->quantity;
        $stock->save();

        OrderLog::create([
            'order_id' => $this->order_id,
            'type' => OrderLogType::STOCK_RELEASED,
            'meta' => [
                'stock_id' => $stock->id,
                'order_item_id' => $this->id,
                'quantity' => $this->quantity
            ]
        ]);
    }

    public function confirmReservedStock()
    {
        $stock = $this->stock;

        // Validate stock can be confirmed
        $orderLogs = OrderLog::where('order_id', $this->order_id)
                             ->where('type', [
                                 OrderLogType::STOCK_RESERVED,
                                 OrderLogType::STOCK_RELEASED,
                                 OrderLogType::STOCK_CONFIRMED,
                                 OrderLogType::STOCK_RETURNED
                             ])
                             ->where('meta->order_item_id', $this->id)
                             ->where('meta->stock_id', $stock->id)
                             ->get();

        $reservedSum = $orderLogs->where('type', OrderLogType::STOCK_RESERVED)
                                 ->sum(fn (OrderLog $log) => $log->meta['quantity']);

        $unconfirmedQuantity = $reservedSum - $orderLogs->where('type', [
            OrderLogType::STOCK_RELEASED,
            OrderLogType::STOCK_CONFIRMED,
            OrderLogType::STOCK_RETURNED,
        ])->sum(fn (OrderLog $log) => $log->meta['quantity']);

        if (
            $unconfirmedQuantity !== $this->quantity ||
            $stock->outgoing_quantity < $this->quantity
        ) {
            throw new InvalidStockQuantity();
        }

        $stock->outgoing_quantity -= $this->quantity;
        $stock->save();

        OrderLog::create([
            'order_id' => $this->order_id,
            'type' => OrderLogType::STOCK_CONFIRMED,
            'meta' => [
                'stock_id' => $stock->id,
                'order_item_id' => $this->id,
                'quantity' => $this->quantity
            ]
        ]);
    }

    public function returnStock($quantity = null)
    {
        $returnQuantity = $quantity ?: $this->quantity;
        $stock = $this->stock;

        // Validate stock can be returned
        $orderLogs = OrderLog::where('order_id', $this->order_id)
                             ->where('type', [
                                 OrderLogType::STOCK_CONFIRMED,
                                 OrderLogType::STOCK_RETURNED
                             ])
                             ->where('meta->order_item_id', $this->id)
                             ->where('meta->stock_id', $stock->id)
                             ->get();

        $confirmedSum = $orderLogs->where('type', OrderLogType::STOCK_CONFIRMED)
                                  ->sum(fn (OrderLog $log) => $log->meta['quantity']);
        $returnedSum = $orderLogs->where('type', OrderLogType::STOCK_RETURNED)
                                 ->sum(fn (OrderLog $log) => $log->meta['quantity']);

        $remainingQuantity = $confirmedSum - $returnedSum;

        if ($remainingQuantity < $returnQuantity) {
            throw new InvalidStockQuantity();
        }

        // Update stock
        $stock->current_quantity += ($this->quantity >= $returnQuantity) ? $returnQuantity : $this->quantity;
        $stock->save();

        OrderLog::create([
            'order_id' => $this->order_id,
            'type' => OrderLogType::STOCK_RETURNED,
            'meta' => [
                'stock_id' => $stock->id,
                'order_item_id' => $this->id,
                'quantity' => $returnQuantity
            ]
        ]);
    }
}
