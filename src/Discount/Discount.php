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
        $parameters['id'] ??= sha1((string)Str::orderedUuid());
        $parameters['attributes'] ??= Collection::make([]);

        parent::__construct($parameters);
    }


    public function getId(): string
    {
        return $this->id;
    }

    public function calculateValue(Money $price): Money
    {
        if ($this->type === DiscountType::Percentage) {
            return $price->multiply((string) ($this->value / 100));
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
        $type = DiscountType::tryFrom($discount['type']);

        return new Discount([
            'type'       => $type,
            'target'     => DiscountTarget::tryFrom($discount['target']),
            'value'      => $type === DiscountType::Value
                                ? new Money($discount['value']['amount'] ?? 0, $discount['value']['currency'] ?? 'USD')
                                : (float)$discount['value'],
            'attributes' => collect($discount['attributes'] ?? []),
        ]);
    }
}
