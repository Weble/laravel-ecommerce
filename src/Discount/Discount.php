<?php

namespace Weble\LaravelEcommerce\Discount;

use Cknow\Money\Money;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Spatie\DataTransferObject\DataTransferObject;

class Discount extends DataTransferObject implements Arrayable, Jsonable
{
    /** @var \Cknow\Money\Money|\Money\Money|float|int */
    public $value;
    public DiscountTarget $target;
    public DiscountType $type;

    public function calculateValue(Money $price): Money
    {
        if ($this->type->equals(DiscountType::percentage())) {
            return $price->multiply($this->value / 100);
        }

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

    public static function fromArray(array $discount): self
    {
        $type = DiscountType::make($discount['type']);

        return new Discount([
            'type'   => $type,
            'target' => DiscountTarget::make($discount['target']),
            'value'  => $type->equals(DiscountType::value()) ? money($discount['value']['amount'] ?? 0, $discount['value']['currency'] ?? 'USD') : (float) $discount['value'],
        ]);
    }
}
