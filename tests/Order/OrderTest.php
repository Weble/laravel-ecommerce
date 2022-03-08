<?php

namespace Weble\LaravelEcommerce\Tests\Order;

use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Order\Order;
use Weble\LaravelEcommerce\Order\OrderBuilder;
use Weble\LaravelEcommerce\Order\OrderState;
use Weble\LaravelEcommerce\Order\OrderTransition;
use Weble\LaravelEcommerce\Payment\Payment;
use Weble\LaravelEcommerce\Payment\PaymentState;
use Weble\LaravelEcommerce\Payment\PaymentTransition;
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
        $total = $cart->total();

        $order = (new OrderBuilder())
            ->fromCart($cart)
            ->create();

        $this->assertEquals(1, Order::query()->count());
        $this->assertEquals($total, $order->total);
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

        $this->assertEquals(OrderState::NEW, $order->stateIs());

        $order->apply(OrderTransition::PAY);

        $this->assertEquals(OrderState::PAYED, $order->stateIs());
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

        $order->apply(OrderTransition::PAY);

        $this->assertEquals(1, $order->stateHistory()->get()->count());

        $order2 = (new OrderBuilder())
            ->fromCart($cart)
            ->create();

        $order2->apply(OrderTransition::PAY);
        $this->assertEquals(1, $order2->stateHistory()->get()->count());
        $this->assertEquals(1, $order->stateHistory()->get()->count());
        $this->assertDatabaseCount('ecommerce_state_history', 2);
    }

    /** @test */
    public function order_creates_payment()
    {
        $product = factory(Product::class)->create(['price' => money(100)]);

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart')->instance();
        $cartItem = $cart->add($product);

        $order = (new OrderBuilder())
            ->fromCart($cart)
            ->create();

        $this->assertEquals(1, $order->payments()->count());

        /** @var Payment $payment */
        $payment = Payment::query()->latest()->first();

        $this->assertTrue($payment->total->equals($order->total));
        $this->assertEquals($order->payment_gateway, $payment->payment_gateway);
        $this->assertEquals($order->currency->getCode(), $payment->currency->getCode());

        $this->assertEquals(OrderState::NEW, $order->stateIs());

        $payment->apply(PaymentTransition::COMPLETE);
        $order->refresh();

        $this->assertEquals(PaymentState::COMPLETED, $payment->stateIs());
        $this->assertEquals(OrderState::PAYED, $order->stateIs());
    }
}
