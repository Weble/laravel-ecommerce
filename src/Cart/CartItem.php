<?php


namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;
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
        return sha1($this->product->cartId() . '-' . $this->attributes->toJson());
    }

    public function subTotal(): Money
    {
        return $this->price->multiply($this->quantity);
    }
}
