<?php

namespace Weble\LaravelEcommerce\Tests\Order;

use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Order\Order;
use Weble\LaravelEcommerce\Order\OrderBuilder;
use Weble\LaravelEcommerce\Tests\mocks\Product;
use Weble\LaravelEcommerce\Tests\TestCase;

class OrderTest extends TestCase
{
    /** @test */
    public function can_create_order_from_cart()
    {
        $product = factory(Product::class)->create(['price' => money(100)]);

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart')->instance();
        $cartItem = $cart->add($product);

        $order = (new OrderBuilder())
            ->fromCart($cart)
            ->create();

        $this->assertDatabaseCount('orders', 1);
        $this->assertEquals($cart->total(), $order->total);
        $this->assertEquals(1, $order->items->count());
        $this->assertEquals($product->getKey(), $order->items->first()->product->getKey());
    }

    /** @test */
    public function order_has_unique_hash()
    {
        $product = factory(Product::class)->create(['price' => money(100)]);

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart')->instance();
        $cartItem = $cart->add($product);

        $order = (new OrderBuilder())
            ->fromCart($cart)
            ->create();

        $this->assertGreaterThan(0, strlen($order->hash));
        $this->assertEquals(1, Order::whereHash($order->hash)->count());
    }

    /** @test */
    public function order_items_are_stored_correctly()
    {
        $product = factory(Product::class)->create(['price' => money(100)]);

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart')->instance();
        $cartItem = $cart->add($product);

        $order = (new OrderBuilder())
            ->fromCart($cart)
            ->create();

        $this->assertDatabaseCount('order_items', 1);

        $this->assertEquals(1, $order->items->count());

        $cart->clear();
        $cartItem = $cart->add($product);

        $order2 = (new OrderBuilder())
            ->fromCart($cart)
            ->create();

        $this->assertEquals(1, $order2->items()->get()->count());
        $this->assertEquals(1, $order->items()->get()->count());
        $this->assertDatabaseCount('order_items', 2);
    }

    /** @test */
    public function uses_state_machine_to_manage_order()
    {
        $product = factory(Product::class)->create(['price' => money(100)]);

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart')->instance();
        $cartItem = $cart->add($product);

        $order = (new OrderBuilder())
            ->fromCart($cart)
            ->create();

        $this->assertEquals('created', $order->stateIs());

        $order->apply('readyForPayment');

        $this->assertEquals('waiting_for_payment', $order->stateIs());
    }

    /** @test */
    public function order_history_is_stored_correctly()
    {
        $product = factory(Product::class)->create(['price' => money(100)]);

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart')->instance();
        $cartItem = $cart->add($product);

        $order = (new OrderBuilder())
            ->fromCart($cart)
            ->create();

        $order->apply('readyForPayment');

        $this->assertEquals(1, $order->stateHistory()->get()->count());

        $order2 = (new OrderBuilder())
            ->fromCart($cart)
            ->create();

        $order2->apply('readyForPayment');
        $this->assertEquals(1, $order2->stateHistory()->get()->count());
        $this->assertEquals(1, $order->stateHistory()->get()->count());
        $this->assertDatabaseCount('order_history', 2);
    }
}
