<?php

namespace Weble\LaravelEcommerce\Tests\Cart;

use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Cart\CartManager;
use Weble\LaravelEcommerce\Cart\CartSessionDriver;
use Weble\LaravelEcommerce\Tests\mocks\Product;
use Weble\LaravelEcommerce\Tests\TestCase;

class CartTest extends TestCase
{
    /** @test */
    public function can_get_cart_manager_from_config()
    {
        $this->assertInstanceOf(CartManager::class, app('ecommerce.cartManager'));
    }

    /** @test */
    public function can_get_cart_instances()
    {
        /** @var CartManager $cart */
        $cart = app('ecommerce.cartManager');

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

    /** @test */
    public function can_add_to_cart()
    {
        $product = new Product(1, money(100));

        /** @var Cart $cart */
        $cart = app('ecommerce.cartManager');
        $cart->add($product, 2);

        $this->assertEquals(2, $cart->items()->total());
    }

    /** @test */
    public function can_remove_from_cart()
    {
        $product = new Product(1, money(100));
        $product2 = new Product(2, money(200));

        /** @var Cart $cart */
        $cart = app('ecommerce.cartManager');
        $cart->add($product, 2);
        $cart->add($product2, 1);

        $cartItem = $cart->items()->first();
        $cart->remove($cartItem);

        $this->assertEquals(1, $cart->items()->total());
    }

    /** @test */
    public function can_calculate_total()
    {
        $product = new Product(1, money(100));
        $product2 = new Product(2, money(200));

        /** @var Cart $cart */
        $cart = app('ecommerce.cartManager');
        $cart->add($product, 2);
        $cart->add($product2, 1);

        $this->assertTrue($cart->subTotal()->equals(money(400)));
    }

    /** @test */
    public function can_calculate_tax()
    {
        // These is tested with 22% IT vat
        $product = new Product(1, money(100));
        $product2 = new Product(2, money(200));

        /** @var Cart $cart */
        $cart = app('ecommerce.cartManager');
        $cart->add($product, 2);
        $cart->add($product2, 1);

        $this->assertTrue($cart->tax()->equals(money(88)));
        $this->assertTrue($cart->total()->equals(money(488)));
    }
}
