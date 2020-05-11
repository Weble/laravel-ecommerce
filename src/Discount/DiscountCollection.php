<?php

namespace Weble\LaravelEcommerce\Discount;

use Cknow\Money\Money;
use Illuminate\Support\Collection;

class DiscountCollection extends Collection
{
    public function withTarget(DiscountTarget $target): self
    {
        return $this->filter(function (DiscountInterface $discount) use ($target) {
            return $discount->target()->isEqual($target);
        });
    }

    public function total(Money $price): Money
    {
        if ($this->count() <= 0) {
            return new Money(0, currencyManager()->defaultCurrency());
        }

        return $this->reduce(function (?Money $sum = null, ?DiscountInterface $discount = null) use ($price) {
            if ($sum === null) {
                return $discount->calculateValue($price);
            }

            return $sum->add($discount->calculateValue($price));
        });
    }
}
