<?php

namespace Weble\LaravelEcommerce\Discount;

use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\LaravelData\Data;

class Discount extends Data
{
    public function __construct(
        public Money|int      $value,
        public ?string        $id = null,
        public DiscountTarget $target = DiscountTarget::Items,
        public DiscountType   $type = DiscountType::Percentage,
        public ?Collection    $attributes = null,
    )
    {
        $this->id ??= sha1((string)Str::orderedUuid());
        $this->attributes ??= Collection::make([]);
    }

    public function calculateValue(Money $price): Money
    {
        if ($this->type === DiscountType::Percentage) {
            return $price->multiply((string)($this->value / 100));
        }

        return $this->value;
    }

    public function target(): DiscountTarget
    {
        return $this->target;
    }

    public static function fromArray(array $discount): self
    {
        $type = DiscountType::tryFrom($discount['type']);

        return new Discount(
            value: $type === DiscountType::Value
                ? new Money($discount['value']['amount'] ?? 0, $discount['value']['currency'] ?? 'USD')
                : (float)$discount['value'],
            target: DiscountTarget::tryFrom($discount['target']),
            type: $type,
            attributes: collect($discount['attributes'] ?? []),
        );
    }
}
