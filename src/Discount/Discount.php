<?php

namespace Weble\LaravelEcommerce\Discount;

use Cknow\Money\Money;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\DataTransferObject\DataTransferObject;

class Discount extends DataTransferObject implements Arrayable, Jsonable
{
    public string $id;
    /** @var \Cknow\Money\Money|\Money\Money|float|int */
    public $value;
    public DiscountTarget $target;
    public DiscountType $type;
    public Collection $attributes;

    public function __construct(array $parameters = [])
    {
        $this->attributes = collect([]);
        $parameters['id'] ??= sha1((string)Str::orderedUuid());

        parent::__construct($parameters);
    }

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
            'type'       => $type,
            'target'     => DiscountTarget::make($discount['target']),
            'value'      => $type->equals(DiscountType::value()) ? money($discount['value']['amount'] ?? 0, $discount['value']['currency'] ?? 'USD') : (float)$discount['value'],
            'attributes' => collect($discount['attributes'] ?? []),
        ]);
    }
}
