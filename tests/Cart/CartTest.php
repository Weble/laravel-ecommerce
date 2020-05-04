<?php

namespace Weble\LaravelEcommerce\Tests\Cart;

use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Cart\CartManager;
use Weble\LaravelEcommerce\Cart\CartSessionDriver;
use Weble\LaravelEcommerce\Tests\TestCase;

class CartTest extends TestCase
{
    /** @test */
    public function can_get_cart_instances()
    {
        /** @var CartManager $cart */
        $cart = app(CartManager::class);

        $this->assertInstanceOf(CartManager::class, $cart);

        $defaultCart = $cart->instance();
        $this->assertInstanceOf(Cart::class, $defaultCart);

        $defaultCart = $cart->instance('cart');
        $this->assertInstanceOf(Cart::class, $defaultCart);
        $this->assertInstanceOf(CartSessionDriver::class, $defaultCart->driver());
        $this->assertEquals('cart', $defaultCart->driver()->instanceName());

        $wishlist = $cart->instance('wishlist');
        $this->assertInstanceOf(Cart::class, $wishlist);
        $this->assertInstanceOf(CartSessionDriver::class, $wishlist->driver());
        $this->assertEquals('wishlist', $wishlist->driver()->instanceName());
    }
}
