<?php

namespace Weble\LaravelEcommerce\Order;

use Weble\LaravelEcommerce\Cart\CartItem;

class OrderItemBuilder
{
    protected OrderItem $orderItem;

    public function __construct()
    {
        $class           = config('ecommerce.classes.orderItemModel', OrderItem::class);
        $this->orderItem = new $class;
    }

    public function fromCartItem(CartItem $cartItem): OrderItemBuilder
    {
        $this->orderItem
            ->fill([
                'quantity'           => $cartItem->quantity,
                'product_attributes' => $cartItem->attributes,
                'purchasable_data'   => $cartItem->product->toJson(),
                'discounts'          => $cartItem->discounts,
                'unit_price'         => $cartItem->unitPrice(),
                'discounts_subtotal' => $cartItem->discount(),
                'subtotal'           => $cartItem->subTotal(),
            ]);

        $this->orderItem
            ->product()
            ->associate($cartItem->product);

        return $this;
    }

    public function make(): OrderItem
    {
        return $this->orderItem;
    }

    public function create(): OrderItem
    {
        $this->orderItem->save();

        return $this->orderItem;
    }
}
