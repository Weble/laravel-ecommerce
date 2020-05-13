<?php

namespace Weble\LaravelEcommerce\Tests\Cart;

use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Cart\CartManager;
use Weble\LaravelEcommerce\Cart\CartSessionDriver;
use Weble\LaravelEcommerce\Discount\DiscountTarget;
use Weble\LaravelEcommerce\Discount\ValueDiscount;
use Weble\LaravelEcommerce\Storage\CacheStorage;
use Weble\LaravelEcommerce\Storage\SessionStorage;
use Weble\LaravelEcommerce\Tests\mocks\Product;
use Weble\LaravelEcommerce\Tests\TestCase;

class CartTest extends TestCase
{
    /** @test */
    public function can_get_cart_manager_from_config()
    {
        $this->assertInstanceOf(CartManager::class, app('ecommerce.cart'));
    }

    /** @test */
    public function can_load_facade()
    {
        $this->assertInstanceOf(CartManager::class, \Weble\LaravelEcommerce\Facades\Cart::getFacadeRoot());
    }

    /**
     * @test
     * @dataProvider driversProvider
     */
    public function can_get_cart_instances($driver, $expectedStorage)
    {
        $this->setCartStorageDriver($driver);

        $defaultCart = \Weble\LaravelEcommerce\Facades\Cart::instance();
        $this->assertInstanceOf(Cart::class, $defaultCart);

        /** @var Cart $defaultCart */
        $defaultCart = \Weble\LaravelEcommerce\Facades\Cart::instance('cart');
        $this->assertInstanceOf(Cart::class, $defaultCart);
        $this->assertInstanceOf($expectedStorage, $defaultCart->storage());
        $this->assertEquals('cart', $defaultCart->instanceName());

        config()->set('ecommerce.cart.instances.wishlist.storage', $driver);

        $wishlist = \Weble\LaravelEcommerce\Facades\Cart::instance('wishlist');
        $this->assertInstanceOf(Cart::class, $wishlist);
        $this->assertInstanceOf($expectedStorage, $wishlist->storage());
        $this->assertEquals('wishlist', $wishlist->instanceName());
    }

    /**
     * @test
     * @dataProvider driversProvider
     */
    public function can_add_to_cart($driver)
    {
        $this->setCartStorageDriver($driver);

        $product = new Product(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);

        $this->assertEquals(2, $cart->items()->total());
    }

    /**
     * @test
     * @dataProvider driversProvider
     */
    public function can_remove_from_cart($driver)
    {
        $this->setCartStorageDriver($driver);

        $product = new Product(['id' => 1, 'price' => money(100)]);
        $product2 = new Product(['id' => 2, 'price' => money(200)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cartItem = $cart->add($product, 2);
        $cart->add($product2, 1);

        $cart->remove($cartItem);

        $this->assertEquals(1, $cart->items()->total());
    }

    /**
     * @test
     * @dataProvider driversProvider
     */
    public function can_calculate_total($driver)
    {
        $this->setCartStorageDriver($driver);

        $product = new Product(['id' => 1, 'price' => money(100)]);
        $product2 = new Product(['id' => 2, 'price' => money(200)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);
        $cart->add($product2, 1);

        $this->assertTrue($cart->subTotal()->equals(money(400)));
    }

    /**
     * @test
     * @dataProvider driversProvider
     */
    public function can_calculate_tax($driver)
    {
        $this->setCartStorageDriver($driver);

        // These is tested with 22% IT vat
        $product = new Product(['id' => 1, 'price' => money(100)]);
        $product2 = new Product(['id' => 2, 'price' => money(200)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);
        $cart->add($product2, 1);

        $this->assertTrue($cart->tax()->equals(money(88)));
        $this->assertTrue($cart->total()->equals(money(488)));
    }

    /**
     * @test
     * @dataProvider driversProvider
     */
    public function can_calculate_item_discounts($driver)
    {
        $this->setCartStorageDriver($driver);

        // These is tested with 22% IT vat
        $product = new Product(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2)->withDiscount(new ValueDiscount([
            'value' => money(10),
            'target' => DiscountTarget::item(),
        ]));

        $this->assertTrue($cart->subTotal()->equals(money(180)));
    }

    /**
     * @test
     * @dataProvider driversProvider
     */
    public function can_calculate_subtotal_discounts($driver)
    {
        $this->setCartStorageDriver($driver);

        // These is tested with 22% IT vat
        $product = new Product(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);

        $cart->withDiscount(new ValueDiscount([
            'value' => money(10),
            'target' => DiscountTarget::subtotal(),
        ]));

        $this->assertTrue($cart->subTotal()->equals(money(190)));
    }

    /**
     * @test
     * @dataProvider driversProvider
     */
    public function can_calculate_items_discounts($driver)
    {
        $this->setCartStorageDriver($driver);

        // These is tested with 22% IT vat
        $product = new Product(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);

        $cart->withDiscount(new ValueDiscount([
            'value' => money(60),
            'target' => DiscountTarget::items(),
        ]));

        $this->assertTrue($cart->subTotal()->equals(money(140)));
    }

    protected function setCartStorageDriver($driver): void
    {
        config()->set('ecommerce.cart.instances.cart.storage', $driver);
    }

    public function driversProvider()
    {
        return [
            ['session', SessionStorage::class],
            ['cache', CacheStorage::class],
        ];
    }
}
