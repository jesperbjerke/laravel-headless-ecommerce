<?php

namespace Bjerke\Ecommerce\Helpers;

use Bjerke\Ecommerce\Enums\DealDiscountType;
use Bjerke\Ecommerce\Exceptions\InvalidDeal;
use Bjerke\Ecommerce\Models\Deal;
use Bjerke\Ecommerce\Models\Price;
use Illuminate\Support\Carbon;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;

class PriceHelper
{
    public static function calculateTotals(
        Price $price,
        string $locale,
        int $quantity = 1,
        Deal $deal = null,
        $validateDealRelation = false
    ): array {
        $priceValue = new Money($price->discounted_value ?: $price->value, new Currency($price->currency));

        if ($deal && self::isDealApplicable($price, $deal, $validateDealRelation)) {
            $priceValue = self::applyDeal($priceValue, $deal);
        }

        $total = $priceValue->multiply($quantity);
        $totalExVat = $total->divide(($price->vat_percentage / 100) + 1);

        $originalPriceValue = new Money($price->value, new Currency($price->currency));
        $originalTotal = $originalPriceValue->multiply($quantity);
        $originalTotalExVat = $originalTotal->divide(($price->vat_percentage / 100) + 1);

        return self::formatPrices($total, $totalExVat, $originalTotal, $originalTotalExVat, $locale);
    }

    public static function isDealApplicable(Price $price, Deal $deal, $validateRelations = false): bool
    {
        $now = Carbon::now();
        $valid = (!$price->discounted_value || config('ecommerce.pricing.allow_deals_on_discounts')) &&
                 $deal->currency === $price->currency &&
                 ($deal->store_id === null || $deal->store_id === $price->store_id) &&
                 ($deal->activates_at && $now->isAfter($deal->activates_at)) &&
                 (!$deal->expires_at || $now->isBefore($deal->expires_at));

        if (!$validateRelations || !$valid) {
            return $valid;
        }

        return $deal->applicableProducts()->where('id', $price->product_id)->exists();
    }

    public static function applyDeal(Money $price, Deal $deal): Money
    {
        if ($deal->discount_type === DealDiscountType::FIXED) {
            return $price->subtract(new Money($deal->discount_value, new Currency($deal->currency)));
        }

        return $price->divide(($deal->discount_value / 100) + 1);
    }

    public static function formatPrices(
        Money $total,
        Money $totalExVat,
        Money $originalTotal,
        Money $originalTotalExVat,
        string $locale
    ): array {
        $moneyFormatter = new IntlMoneyFormatter(
            new \NumberFormatter($locale, \NumberFormatter::CURRENCY),
            new ISOCurrencies()
        );

        return [
            'total' => $total->getAmount(),
            'total_ex_vat' => $totalExVat->getAmount(),
            'original_total' => $originalTotal->getAmount(),
            'original_total_ex_vat' => $originalTotalExVat->getAmount(),

            'formatted' => [
                'total' => $moneyFormatter->format($total),
                'total_ex_vat' => $moneyFormatter->format($totalExVat),
                'original_total' => $moneyFormatter->format($originalTotal),
                'original_total_ex_vat' => $moneyFormatter->format($originalTotalExVat)
            ]
        ];
    }
}
