<?php

namespace Weble\LaravelEcommerce\Discount;

use Cknow\Money\Money;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Spatie\DataTransferObject\DataTransferObject;

class PercentageDiscount extends DataTransferObject implements Arrayable, Jsonable, DiscountInterface
{
    public float $percentage;
    public DiscountTarget $target;

    public function type(): DiscountType
    {
        return DiscountType::percentage();
    }

    public function calculateValue(Money $price): Money
    {
        return $price->multiply($this->percentage / 100);
    }

    public function target(): DiscountTarget
    {
        return $this->target;
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
