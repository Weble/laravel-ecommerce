<?php

namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use CommerceGuys\Addressing\AddressInterface;
use CommerceGuys\Tax\Model\TaxRateAmount;
use CommerceGuys\Tax\Resolver\Context;
use CommerceGuys\Tax\Resolver\TaxResolver;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;
use Weble\LaravelEcommerce\Address\StoreAddress;
use Weble\LaravelEcommerce\Purchasable;

class CartItem extends DataTransferObject implements Arrayable, Jsonable
{
    public Purchasable $product;
    public float $quantity = 1;
    public Money $price;
    public Collection $attributes;

    public function __construct(array $parameters = [])
    {
        $this->attributes = collect([]);

        parent::__construct($parameters);
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public static function fromPurchasable(Purchasable $purchasable, float $quantity = 1, ?Collection $attributes = null): self
    {
        if ($attributes === null) {
            $attributes = collect([]);
        }

        return new static([
            'product' => $purchasable,
            'attributes' => $attributes,
            'quantity' => $quantity,
            'price' => $purchasable->cartPrice($attributes),
        ]);
    }

    public function getId(): string
    {
        return sha1($this->product->getKey() . '-' . $this->attributes->toJson());
    }

    public function subTotal(): Money
    {
        return $this->price->multiply($this->quantity);
    }

    public function tax(AddressInterface $address): Money
    {
        return $this->unitTax($address)->multiply($this->quantity);
    }

    public function total(AddressInterface $address): Money
    {
        return $this->tax($address)->add($this->subTotal());
    }

    public function unitPrice(): Money
    {
        return $this->price;
    }

    public function unitTax(AddressInterface $address): Money
    {
        return taxManager()->taxFor($this->product, $address);
    }

    public function unitTotal(AddressInterface $address): Money
    {
        return $this->unitPrice()->add($this->unitTax($address));
    }
}
