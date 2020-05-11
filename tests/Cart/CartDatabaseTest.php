<?php

namespace Weble\LaravelEcommerce\Tests\Cart;

use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Cart\CartDatabaseDriver;
use Weble\LaravelEcommerce\Cart\CartSessionDriver;
use Weble\LaravelEcommerce\Tests\mocks\Product;
use Weble\LaravelEcommerce\Tests\TestCase;

class CartDatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('ecommerce.cart.instances.cart.driver', 'database');
    }

    /** @test */
    public function can_get_cart_instances()
    {
        $defaultCart = \Weble\LaravelEcommerce\Facades\Cart::instance();
        $this->assertInstanceOf(Cart::class, $defaultCart);

        $defaultCart = \Weble\LaravelEcommerce\Facades\Cart::instance('cart');
        $this->assertInstanceOf(Cart::class, $defaultCart);
        $this->assertInstanceOf(CartDatabaseDriver::class, $defaultCart->driver());
        $this->assertEquals('cart', $defaultCart->driver()->instanceName());

        $wishlist = \Weble\LaravelEcommerce\Facades\Cart::instance('wishlist');
        $this->assertInstanceOf(Cart::class, $wishlist);
        $this->assertInstanceOf(CartSessionDriver::class, $wishlist->driver());
        $this->assertEquals('wishlist', $wishlist->driver()->instanceName());
    }

    /** @test */
    public function can_add_to_cart()
    {
        $product = new Product([
            'id' => 1,
            'price' => money(100),
        ]);
        $product->save();

        /** @var Cart $cart */
        $cart = app('ecommerce.cartManager');
        $cart->add($product, 2);

        $this->assertEquals(2, $cart->items()->total());
    }

    /** @test */
    public function can_remove_from_cart()
    {
        $product = new Product(['id' => 1,
            'price' => money(100),
        ]);
        $product2 = new Product(['id' => 2,
            'price' => money(200),
        ]);

        $product->save();
        $product2->save();

        /** @var Cart $cart */
        $cart = app('ecommerce.cartManager');
        $cart->add($product, 2);
        $cart->add($product2, 1);

        $cartItem = $cart->items()->first();
        $cart->remove($cartItem);

        $this->assertEquals(3, $cart->items()->total());
    }
}
