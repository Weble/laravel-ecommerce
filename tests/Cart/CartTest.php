<?php

namespace Weble\LaravelEcommerce\Tests\Cart;

use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Cart\CartManager;
use Weble\LaravelEcommerce\Cart\CartSessionDriver;
use Weble\LaravelEcommerce\Discount\DiscountTarget;
use Weble\LaravelEcommerce\Discount\ValueDiscount;
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
    public function can_load_facade()
    {
        $this->assertInstanceOf(CartManager::class, \Weble\LaravelEcommerce\Facades\Cart::getFacadeRoot());
    }

    /** @test */
    public function can_get_cart_instances()
    {
        $defaultCart = \Weble\LaravelEcommerce\Facades\Cart::instance();
        $this->assertInstanceOf(Cart::class, $defaultCart);

        $defaultCart = \Weble\LaravelEcommerce\Facades\Cart::instance('cart');
        $this->assertInstanceOf(Cart::class, $defaultCart);
        $this->assertInstanceOf(CartSessionDriver::class, $defaultCart->driver());
        $this->assertEquals('cart', $defaultCart->driver()->instanceName());

        $wishlist = \Weble\LaravelEcommerce\Facades\Cart::instance('wishlist');
        $this->assertInstanceOf(Cart::class, $wishlist);
        $this->assertInstanceOf(CartSessionDriver::class, $wishlist->driver());
        $this->assertEquals('wishlist', $wishlist->driver()->instanceName());
    }

    /** @test */
    public function can_add_to_cart()
    {
        $product = new Product(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cartManager');
        $cart->add($product, 2);

        $this->assertEquals(2, $cart->items()->total());
    }

    /** @test */
    public function can_remove_from_cart()
    {
        $product = new Product(['id' => 1, 'price' => money(100)]);
        $product2 = new Product(['id' => 2, 'price' => money(200)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cartManager');
        $cartItem = $cart->add($product, 2);
        $cart->add($product2, 1);

        $cart->remove($cartItem);

        $this->assertEquals(1, $cart->items()->total());
    }

    /** @test */
    public function can_calculate_total()
    {
        $product = new Product(['id' => 1, 'price' => money(100)]);
        $product2 = new Product(['id' => 2, 'price' => money(200)]);

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
        $product = new Product(['id' => 1, 'price' => money(100)]);
        $product2 = new Product(['id' => 2, 'price' => money(200)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cartManager');
        $cart->add($product, 2);
        $cart->add($product2, 1);

        $this->assertTrue($cart->tax()->equals(money(88)));
        $this->assertTrue($cart->total()->equals(money(488)));
    }

    /** @test */
    public function can_calculate_item_discounts()
    {
        // These is tested with 22% IT vat
        $product = new Product(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cartManager');
        $cart->add($product, 2)->withDiscount(new ValueDiscount([
            'value' => money(10),
            'target' => DiscountTarget::item(),
        ]));

        $this->assertTrue($cart->subTotal()->equals(money(180)));
    }

    /** @test */
    public function can_calculate_subtotal_discounts()
    {
        // These is tested with 22% IT vat
        $product = new Product(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cartManager');
        $cart->add($product, 2);

        $cart->withDiscount(new ValueDiscount([
            'value' => money(10),
            'target' => DiscountTarget::subtotal(),
        ]));

        $this->assertTrue($cart->subTotal()->equals(money(190)));
    }

    /** @test */
    public function can_calculate_items_discounts()
    {
        // These is tested with 22% IT vat
        $product = new Product(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cartManager');
        $cart->add($product, 2);

        $cart->withDiscount(new ValueDiscount([
            'value' => money(60),
            'target' => DiscountTarget::items(),
        ]));

        $this->assertTrue($cart->subTotal()->equals(money(140)));
    }
}
