<?php

namespace Weble\LaravelEcommerce\Discount;

use Cknow\Money\Money;
use Illuminate\Support\Collection;

class DiscountCollection extends Collection
{
    public function withTarget(DiscountTarget $target): self
    {
        return $this->filter(function (Discount $discount) use ($target) {
            return $discount->target()->equals($target);
        });
    }

    public function total(Money $price): Money
    {
        if ($this->filter()->count() <= 0) {
            return new Money(0, currencyManager()->defaultCurrency());
        }

        return $this->filter()->reduce(function (?Money $sum = null, ?Discount $discount = null) use ($price) {
            if ($sum === null) {
                return $discount->calculateValue($price);
            }

            return $sum->add($discount->calculateValue($price));
        });
    }
}
