<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Bjerke\Ecommerce\Exceptions\CartHasExpired;
use Bjerke\Ecommerce\Helpers\PriceHelper;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Money\Currency;
use Money\Money;

class Cart extends BreadModel
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'meta' => 'array'
    ];

    public static function boot(): void
    {
        parent::boot();
        static::creating(static function (Cart $cart) {
            $cart->setAttribute($cart->getKeyName(), Str::uuid());
        });
    }

    protected function define()
    {
        $this->addFieldHasOne(
            'store',
            Lang::get('ecommerce::models.store.singular'),
            self::$FIELD_OPTIONAL,
            'name',
            null,
            [
                'extra_data' => [
                    'prefetch' => true
                ]
            ]
        );

        $this->addFieldSelect(
            'currency',
            Lang::get('ecommerce::fields.currency'),
            self::$FIELD_REQUIRED,
            config('ecommerce.currencies.available'),
            [
                'default' => config('ecommerce.currencies.default')
            ]
        );
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(config('ecommerce.models.cart_item'));
    }

    public function getTotalsAttribute(): array
    {
        return $this->calculateTotals();
    }

    public function calculateTotals(): array
    {
        $this->loadMissing(['cartItems.product.activeDeals', 'cartItems.product.prices']);

        $currency = new Currency($this->currency);

        if ($this->cartItems->isEmpty()) {
            $defaultValue = new Money(0, $currency);

            return PriceHelper::formatPrices(
                $defaultValue,
                $defaultValue,
                $defaultValue,
                $defaultValue,
                App::getLocale()
            );
        }

        $total = new Money(0, $currency);
        $totalExVat = new Money(0, $currency);
        $originalTotal = new Money(0, $currency);
        $originalTotalExVat = new Money(0, $currency);

        $this->cartItems->each(
            function (CartItem $cartItem) use (
                &$total,
                &$totalExVat,
                &$originalTotal,
                &$originalTotalExVat,
                $currency
            ) {
                $total->add(new Money($cartItem->totals['total'], $currency));
                $totalExVat->add(new Money($cartItem->totals['total_ex_vat'], $currency));
                $originalTotal->add(new Money($cartItem->totals['original_total'], $currency));
                $originalTotalExVat->add(new Money($cartItem->totals['original_total_ex_vat'], $currency));
            }
        );

        return PriceHelper::formatPrices(
            $total,
            $totalExVat,
            $originalTotal,
            $originalTotalExVat,
            App::getLocale()
        );
    }

    public function validateCart(): bool
    {
        $this->loadMissing(['cartItems.product.stocks', 'cartItems.product.prices']);

        $errors = [];

        try {
            $ttl = Carbon::now()->subMinutes(config('ecommerce.cart.ttl'));
            if ($this->updated_at->isBefore($ttl)) {
                throw new CartHasExpired();
            }
        } catch (\Throwable $error) {
            if (!isset($errors['cart'])) {
                $errors['cart'] = [];
            }

            $errors['cart'][] = $error->getMessage();
        }

        $this->cartItems->each(function (CartItem $cartItem) use (&$errors) {
            try {
                $cartItem->validateContents($this->currency, $this->store_id);
            } catch (\Throwable $error) {
                if (!isset($errors['cart_items'])) {
                    $errors['cart_items'] = [];
                }

                if (!isset($errors['cart_items'][$cartItem->id])) {
                    $errors['cart_items'][$cartItem->id] = [];
                }

                $errors['cart_items'][$cartItem->id][] = $error->getMessage();
            }
        });

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return true;
    }
}
