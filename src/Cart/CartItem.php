<?php

namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use CommerceGuys\Addressing\AddressInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;
use Weble\LaravelEcommerce\Discount\Discount;
use Weble\LaravelEcommerce\Discount\DiscountCollection;
use Weble\LaravelEcommerce\Discount\DiscountTarget;
use Weble\LaravelEcommerce\Discount\InvalidDiscountException;
use Weble\LaravelEcommerce\Purchasable;

class CartItem extends DataTransferObject implements Arrayable, Jsonable
{
    public Purchasable $product;
    public float $quantity = 1;
    public Money $price;
    public Collection $attributes;
    public DiscountCollection $discounts;

    public function __construct(array $parameters = [])
    {
        $this->attributes = collect([]);
        $this->discounts  = DiscountCollection::make([]);

        parent::__construct($parameters);
    }

    public function withDiscount(Discount $discount): self
    {
        if (! $discount->target()->equals(DiscountTarget::item())) {
            throw new InvalidDiscountException();
        }

        $this->discounts->add($discount);

        return $this;
    }

    public function clearDiscounts(): self
    {
        $this->discounts = DiscountCollection::make([]);

        return $this;
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
            'product'    => $purchasable,
            'attributes' => $attributes,
            'quantity'   => $quantity,
            'price'      => $purchasable->cartPrice($attributes),
        ]);
    }

    public function getId(): string
    {
        return sha1($this->product->getKey() . '-' . $this->attributes->toJson());
    }

    public function subTotalWithoutDiscounts(): Money
    {
        return $this->price->multiply($this->quantity);
    }

    public function subTotal(): Money
    {
        return $this->subTotalWithoutDiscounts()->subtract($this->discount());
    }

    public function discount(): Money
    {
        return $this->discounts->total($this->subTotalWithoutDiscounts(), DiscountTarget::item())->multiply($this->quantity);
    }

    public function tax(AddressInterface $address): Money
    {
        return taxManager()->taxFor($this->product, $this->subTotal(), $address);
    }

    public function total(AddressInterface $address): Money
    {
        return $this->tax($address)->add($this->subTotal());
    }

    public function unitPriceWithoutDiscounts(): Money
    {
        return $this->price;
    }

    public function unitDiscount(): Money
    {
        return $this->discounts->total($this->unitPriceWithoutDiscounts(), DiscountTarget::item());
    }

    public function unitPrice(): Money
    {
        return $this->unitPriceWithoutDiscounts()->subtract($this->unitDiscount());
    }

    public function unitTax(AddressInterface $address): Money
    {
        return $this->tax($address)->divide($this->quantity);
    }

    public function unitTotal(AddressInterface $address): Money
    {
        return $this->unitPrice()->add($this->unitTax($address));
    }
}
