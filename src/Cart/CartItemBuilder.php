<?php

namespace Weble\LaravelEcommerce\Cart;

use Cknow\Money\Money;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Weble\LaravelEcommerce\Purchasable;

/**
 * Class CartItemBuilder
 * @package Weble\LaravelEcommerce\Cart
 * @see CartDriverInterface
 */
class CartItemBuilder
{
    protected Purchasable $product;
    protected float $quantity = 1.0;
    protected ?Money $price = null;
    protected array $attributes = [];
    protected Cart $cart;

    public function __construct()
    {
        $this->cart = $cart;
        $this->id = (string)Str::uuid();
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param mixed|Purchasable $product
     * @return $this
     */
    public function withProduct($product): self
    {
        $this->product = $product;

        if ($product instanceof Purchasable) {
            $this->withId($product->cartId());
            $this->withPrice($product->cartPrice());
            $this->withAttributes($product->cartAttributes());
        }

        return $this;
    }

    public function withQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function withPrice(Money $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function withAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    public function create(): CartItem
    {
        $validator = Validator::make($this->getData(), [
            'id' => [
                'required',
                'uuid',
            ],
            'product' => ['required'],
            'price' => ['required'],
            'quantity' => [
                'required',
                'numeric',
                'min:0',
            ],
            'attributes' => ['array'],
        ]);

        if (! $validator->validate()) {
            throw new ValidationException($validator);
        }

        return new CartItem([
            'id' => $this->id,
            'product' => $this->product,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'attributes' => $this->attributes,
        ]);
    }

    public function toCart(Cart $cart)
    {
        return $cart->driver()->get($this->product);
    }
}
