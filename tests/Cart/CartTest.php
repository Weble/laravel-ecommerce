<?php

namespace Weble\LaravelEcommerce\Tests\Cart;

use Weble\LaravelEcommerce\Address\Address;
use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Cart\CartItemModel;
use Weble\LaravelEcommerce\Cart\CartManager;
use Weble\LaravelEcommerce\Customer\Customer;
use Weble\LaravelEcommerce\Discount\Discount;
use Weble\LaravelEcommerce\Discount\DiscountTarget;
use Weble\LaravelEcommerce\Discount\DiscountType;
use Weble\LaravelEcommerce\Storage\CacheStorage;
use Weble\LaravelEcommerce\Storage\EloquentStorage;
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
     */
    public function can_generate_cart_items_ids_consistently()
    {
        $product = factory(Product::class)->create(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cartItem = $cart->add($product);

        $restoredCartitem = (new CartItemModel())->fromCartValue($cartItem, 'ecommerce', 'cart')->toCartValue();

        $this->assertEquals($cartItem->getId(), $restoredCartitem->getId());
        $this->assertEquals($cartItem->toArray(), $restoredCartitem->toArray());
    }

    /**
     * @test
     * @dataProvider driversProvider
     */
    public function can_add_to_cart($driver)
    {
        $this->setCartStorageDriver($driver);

        $product = factory(Product::class)->create(['price' => money(100)]);

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

        $product = factory(Product::class)->create(['price' => money(100)]);
        $product2 = factory(Product::class)->create(['price' => money(200)]);

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

        $product = factory(Product::class)->create(['price' => money(100)]);
        $product2 = factory(Product::class)->create(['price' => money(200)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);
        $cart->add($product2, 1);

        $this->assertTrue($cart->subTotal()->equals(money(400)), $cart->subTotal());
    }

    /**
     * @test
     * @dataProvider driversProvider
     */
    public function can_calculate_tax($driver)
    {
        $this->setCartStorageDriver($driver);

        // These is tested with 22% IT vat
        $product = factory(Product::class)->create(['price' => money(100)]);
        $product2 = factory(Product::class)->create(['price' => money(200)]);

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
        $product = factory(Product::class)->create(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cartItem = $cart->add($product, 2)->withDiscount(new Discount([
            'value' => money(10),
            'type' => DiscountType::value(),
            'target' => DiscountTarget::item(),
        ]));
        $cart->update($cartItem);

        $this->assertTrue($cart->subTotal()->equals(money(180)), $cart->subTotal());
    }

    /**
     * @test
     * @dataProvider driversProvider
     */
    public function can_calculate_subtotal_discounts($driver)
    {
        $this->setCartStorageDriver($driver);

        // These is tested with 22% IT vat
        $product = factory(Product::class)->create(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);

        $cart->withDiscount(new Discount([
            'value' => money(10),
            'type' => DiscountType::value(),
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
        $product = factory(Product::class)->create(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);

        $cart->withDiscount(new Discount([
            'type' => DiscountType::value(),
            'value' => money(60),
            'target' => DiscountTarget::items(),
        ]));

        $this->assertTrue($cart->subTotal()->equals(money(140)));
    }

    /**
     * @test
     * @dataProvider driversProvider
     */
    public function can_attach_customer_to_cart($driver)
    {
        $this->setCartStorageDriver($driver);

        // These is tested with 22% IT vat
        $product = factory(Product::class)->create(['price' => money(100)]);

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);

        $this->assertTrue(! $cart->tax()->isZero());

        $customer = new Customer([]);
        $customer->billingAddress = new Address([
            'country' => 'IT',
            'state' => 'VI',
        ]);
        $customer->shippingAddress = new Address([
            'country' => 'US',
            'state' => 'NY',
        ]);

        $cart->forCustomer($customer);

        $this->assertTrue($cart->tax()->isZero());
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
            ['eloquent', EloquentStorage::class],
        ];
    }
}
