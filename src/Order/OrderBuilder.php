<?php

namespace Weble\LaravelEcommerce\Order;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Cart\CartItem;

class OrderBuilder
{
    protected Order $order;
    protected Collection $items;

    public function __construct()
    {
        $class         = config('ecommerce.classes.orderModel', Order::class);
        $this->order   = new $class;
        $this->items   = Collection::make([]);

        $this->order->fill([
            'payment_gateway' => config('ecommerce.payment.gateway', config('omnipay.gateway', env('OMNIPAY_GATEWAY', 'PayPal_Express'))),
        ]);
    }

    public function fromCart(Cart $cart): self
    {
        $this->order
            ->fill([
                'id'                 => Str::orderedUuid(),
                'user_id'            => $cart->customer()->user ? $cart->customer()->user->id : null,
                'customer_id'        => $cart->customer()->getId() ?: null,
                'customer'           => $cart->customer(),
                'currency'           => $cart->total()->getMoney()->getCurrency()->getCode(),
                'discounts'          => $cart->discounts(),
                'discounts_subtotal' => $cart->discount(),
                'items_subtotal'     => $cart->itemsSubtotal(),
                'subtotal'           => $cart->subTotal(),
                'tax'                => $cart->tax(),
                'total'              => $cart->total(),
                'state'              => 'created',
            ]);

        $this->items = $cart->items()->map(function (CartItem $item) {
            $item = OrderItem::fromCartItem($item)
                ->make();
            $item->order_id = (string) $this->order->id;

            return $item;
        })->toBase();

        return $this;
    }

    public function withGateway(string $gateway): self
    {
        $this->order->fill([
            'payment_gateway' => $gateway,
        ]);

        return $this;
    }

    public function make(): Order
    {
        return $this->order;
    }

    public function create(): Order
    {
        DB::transaction(function () {
            $this->order->save();
            $this->items->each->save();
        });

        if (config('ecommerce.order.clear_cart', true)) {
            cartManager()->clear();
        }

        return $this->order;
    }
}
