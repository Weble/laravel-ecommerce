<?php

namespace Weble\LaravelEcommerce\Discount;

use Cknow\Money\Money;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Spatie\DataTransferObject\DataTransferObject;

class ValueDiscount extends DataTransferObject implements DiscountInterface, Arrayable, Jsonable
{
    public Money $value;
    public DiscountTarget $target;

    public function type(): DiscountType
    {
        return DiscountType::value();
    }

    public function calculateValue(Money $price): Money
    {
        return $this->value;
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
