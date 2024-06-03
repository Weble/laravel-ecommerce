<?php

namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use CommerceGuys\Addressing\AddressInterface;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Weble\LaravelEcommerce\Discount\Discount;
use Weble\LaravelEcommerce\Discount\DiscountCollection;
use Weble\LaravelEcommerce\Discount\DiscountTarget;
use Weble\LaravelEcommerce\Discount\InvalidDiscountException;
use Weble\LaravelEcommerce\Purchasable;

class CartItem extends Data
{
    public function __construct(
        public Purchasable        $product,
        public Money              $price,
        public Collection         $attributes,
        public DiscountCollection $discounts,
        public float              $quantity = 1,
    )
    {
    }

    public function withDiscount(Discount $discount): self
    {
        if ($discount->target !== DiscountTarget::Item) {
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


    public static function fromPurchasable(Purchasable $purchasable, float $quantity = 1, ?Collection $attributes = null, ?Money $price = null): self
    {
        if ($attributes === null) {
            $attributes = collect([]);
        }

        $price ??= $purchasable->cartPrice($attributes);

        return new static(
            product: $purchasable,
            price: $price,
            attributes: $attributes,
            discounts: DiscountCollection::make([]),
            quantity: $quantity,
        );
    }

    public function getId(): string
    {
        return sha1(implode("-", [
            $this->morphAlias($this->product),
            $this->product->getKey(),
            $this->attributes->toJson()
        ]));
    }

    private function morphAlias(Purchasable $product): string
    {
        $class = get_class($product);
        foreach (Relation::$morphMap as $alias => $model) {
            if ($model === $class) {
                return $alias;
            }
        }

        return $class;
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
        return $this->discounts->total($this->subTotalWithoutDiscounts(), DiscountTarget::Item)->multiply($this->quantity);
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
        return $this->discounts->total($this->unitPriceWithoutDiscounts(), DiscountTarget::Item);
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
