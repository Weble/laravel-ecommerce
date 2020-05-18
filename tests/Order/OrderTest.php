<?php


namespace Weble\LaravelEcommerce\Tests\Order;


use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Order\Order;
use Weble\LaravelEcommerce\Tests\mocks\Product;
use Weble\LaravelEcommerce\Tests\TestCase;

class OrderTest extends TestCase
{
    /** @test */
    public function can_create_order_from_cart()
    {
        $product = factory(Product::class)->create(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart')->instance();
        $cartItem = $cart->add($product);

        $order = Order::createFromCart($cart);
        $order->save();

        $this->assertDatabaseCount('orders', 1);
        $this->assertEquals($cart->total(), $order->total);
        $this->assertEquals(1, $order->items->count());
        $this->assertEquals($product->getKey(), $order->items->first()->product->getKey());
    }

}
