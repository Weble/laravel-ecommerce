<?php

namespace Weble\LaravelEcommerce\Tests\Cart;

use Cknow\Money\Money;
use Money\Currency;
use Weble\LaravelEcommerce\Address\Address;
use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Cart\CartItemModel;
use Weble\LaravelEcommerce\Cart\CartManager;
use Weble\LaravelEcommerce\Customer\Customer;
use Weble\LaravelEcommerce\Discount\Discount;
use Weble\LaravelEcommerce\Discount\DiscountModel;
use Weble\LaravelEcommerce\Discount\DiscountTarget;
use Weble\LaravelEcommerce\Discount\DiscountType;
use Weble\LaravelEcommerce\Storage\CacheStorage;
use Weble\LaravelEcommerce\Storage\EloquentStorage;
use Weble\LaravelEcommerce\Storage\SessionStorage;
use Weble\LaravelEcommerce\Tests\factories\ProductFactory;
use Weble\LaravelEcommerce\Tests\factories\UserFactory;
use Weble\LaravelEcommerce\Tests\mocks\Product;
use Weble\LaravelEcommerce\Tests\mocks\User;
use Weble\LaravelEcommerce\Tests\TestCase;

class CartTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_get_cart_manager_from_config()
    {
        $this->assertInstanceOf(CartManager::class, app('ecommerce.cart'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_load_facade()
    {
        $this->assertInstanceOf(CartManager::class, \Weble\LaravelEcommerce\Facades\Cart::getFacadeRoot());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('driversProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_generate_cart_items_ids_consistently()
    {
        $product = ProductFactory::new(['price' => money(100)])->create();

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart');
        $cartItem = $cart->add($product);

        $restoredCartitem = (new CartItemModel())->fromCartValue($cartItem, 'ecommerce', 'cart')->toCartValue();

        $this->assertEquals($cartItem->getId(), $restoredCartitem->getId());
        $this->assertEquals($cartItem->toArray(), $restoredCartitem->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('driversProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_add_to_cart($driver)
    {
        $this->setCartStorageDriver($driver);

        $product = ProductFactory::new(['price' => money(100)])->create();

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart');
        $cartItem = $cart->add($product, 2);

        $this->assertEquals(2, $cart->items()->total());

        $retrievedCartItem = $cart->get($cartItem->getId());

        $this->assertEquals($cartItem, $retrievedCartItem);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('driversProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_check_if_item_is_in_cart($driver)
    {
        $this->setCartStorageDriver($driver);

        $product  = ProductFactory::new(['price' => money(100)])->create();

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart');
        $cartItem = $cart->add($product, 1);

        $this->assertTrue($cart->has($cartItem->getId()));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('driversProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_remove_from_cart($driver)
    {
        $this->setCartStorageDriver($driver);

        $product  = ProductFactory::new(['price' => money(100)])->create();
        $product2 = ProductFactory::new(['price' => money(200)])->create();

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart');
        $cartItem = $cart->add($product, 2);
        $cart->add($product2, 1);
        $cart->remove($cartItem);

        $this->assertEquals(1, $cart->items()->total());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('driversProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_calculate_total($driver)
    {
        $this->setCartStorageDriver($driver);

        $product  = ProductFactory::new(['price' => money(100)])->create();
        $product2 = ProductFactory::new(['price' => money(200)])->create();

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);
        $cart->add($product2, 1);

        $this->assertTrue($cart->subTotal()->equals(money(400)), $cart->subTotal());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('driversProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_calculate_tax($driver)
    {
        $this->setCartStorageDriver($driver);

        // These are tested with 22% IT vat
        $product  = ProductFactory::new(['price' => money(100)])->create();
        $product2 = ProductFactory::new(['price' => money(200)])->create();

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);
        $cart->add($product2, 1);

        $this->assertTrue($cart->tax()->equals(money(88)));
        $this->assertTrue($cart->total()->equals(money(488)));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('driversProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_calculate_item_discounts($driver)
    {
        $this->setCartStorageDriver($driver);

        // These is tested with 22% IT vat
        $product = ProductFactory::new(['price' => money(100)])->create();

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart');
        $cartItem = $cart->add($product, 2)->withDiscount(new Discount([
            'value'  => money(10),
            'type'   => DiscountType::Value,
            'target' => DiscountTarget::Item,
        ]));
        $cart->update($cartItem);

        $this->assertTrue($cart->subTotal()->equals(money(180)), $cart->subTotal());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('driversProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_calculate_subtotal_discounts($driver)
    {
        $this->setCartStorageDriver($driver);

        // These is tested with 22% IT vat
        $product = ProductFactory::new(['price' => money(100)])->create();

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);

        $cart->withDiscount(new Discount([
            'value'  => money(10),
            'type'   => DiscountType::Value,
            'target' => DiscountTarget::Subtotal,
        ]));

        $this->assertTrue($cart->subTotal()->equals(money(190)));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('driversProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_calculate_items_discounts($driver)
    {
        $this->setCartStorageDriver($driver);

        // These is tested with 22% IT vat
        $product = ProductFactory::new(['price' => money(100)])->create();

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);

        $cart->withDiscount(new Discount([
            'type'   => DiscountType::Value,
            'value'  => money(60),
            'target' => DiscountTarget::Items,
        ]));

        $this->assertTrue($cart->subTotal()->equals(money(140)));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('driversProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function can_attach_customer_to_cart($driver)
    {
        $this->setCartStorageDriver($driver);

        // These is tested with 22% IT vat
        $product = ProductFactory::new(['price' => money(100)])->create();

        /** @var Cart $cart */
        $cart = app('ecommerce.cart');
        $cart->add($product, 2);

        $this->assertTrue(! $cart->tax()->isZero());

        $customer                 = new Customer([]);
        $customer->billingAddress = new Address([
            'country' => 'IT',
            'state'   => 'VI',
        ]);
        $customer->shippingAddress = new Address([
            'country' => 'US',
            'state'   => 'NY',
        ]);

        $cart->forCustomer($customer);

        $this->assertTrue($cart->tax()->isZero());
    }

    protected function setCartStorageDriver($driver): void
    {
        config()->set('ecommerce.cart.instances.cart.storage', $driver);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_add_to_cart_storing_in_db()
    {
        $this->setCartStorageDriver('eloquent');

        $product = ProductFactory::new(['price' => money(100)])->create();

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart');
        $cartItem = $cart->add($product, 2);

        /** @var CartItemModel $storedCartItem */
        $storedCartItem = CartItemModel::query()->latest()->first();
        $this->assertEquals(2, $storedCartItem->quantity);

        $this->assertEquals($product->getKey(), $storedCartItem->product->getKey());
        $this->assertTrue($product->price->equals($storedCartItem->price));

        $cart->clear();

        $this->assertEquals(0, CartItemModel::query()->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_add_to_cart_storing_in_db()
    {
        $this->setCartStorageDriver('eloquent');

        /** @var User $user */
        $user = UserFactory::new()->create();
        $this->actingAs($user);

        $product = ProductFactory::new(['price' => money(100)])->create();

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart');
        $cartItem = $cart->add($product, 2);

        /** @var CartItemModel $storedCartItem */
        $storedCartItem = CartItemModel::query()->latest()->first();
        $this->assertEquals(2, $storedCartItem->quantity);
        $this->assertEquals($product->getKey(), $storedCartItem->product->getKey());
        $this->assertTrue($product->price->equals($storedCartItem->price));
        $this->assertEquals($user->id, $storedCartItem->user_id);

        $cart->clear();

        $this->assertEquals(0, CartItemModel::query()->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cart_stored_storing_in_db_for_different_users()
    {
        $this->setCartStorageDriver('eloquent');

        /** @var User $user */
        $user = UserFactory::new()->create();
        /** @var User $otherUser */
        $otherUser = UserFactory::new()->create();
        /** @var Product $product */
        $product = ProductFactory::new(['price' => money(100)])->create();

        $this->actingAs($user);

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart');
        $cartItem = $cart->add($product, 2);

        /** @var CartItemModel $storedCartItem */
        $storedCartItem = CartItemModel::query()->latest()->first();
        $this->assertEquals(2, $storedCartItem->quantity);
        $this->assertEquals($product->getKey(), $storedCartItem->product->getKey());
        $this->assertTrue($product->price->equals($storedCartItem->price));
        $this->assertEquals($user->id, $storedCartItem->user_id);

        session()->regenerate(true);
        $this->actingAs($otherUser);
        app()->forgetInstance('ecommerce.cart');

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart');
        $cartItem = $cart->add($product, 3);

        /** @var CartItemModel $storedCartItem */
        $storedCartItem = CartItemModel::query()->forCurrentUser()->latest()->first();
        $this->assertEquals(3, $storedCartItem->quantity);
        $this->assertEquals($product->getKey(), $storedCartItem->product->getKey());
        $this->assertTrue($product->price->equals($storedCartItem->price));
        $this->assertEquals($otherUser->id, $storedCartItem->user_id);

        $cart->clear();

        $this->assertEquals(1, CartItemModel::query()->count());
        $this->assertEquals(1, CartItemModel::query()->where('user_id', '=', $user->id)->count());
        $this->assertEquals(0, CartItemModel::query()->where('user_id', '=', $otherUser->id)->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_add_discount_to_cart_storing_in_db()
    {
        $this->setCartStorageDriver('eloquent');

        $product = ProductFactory::new(['price' => money(100)])->create();
        $value   = new Money(1, new Currency('EUR'));
        $options = collect(['code' => 'CODE']);

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart');
        $cartItem = $cart->add($product, 2);
        $cart->withDiscount(new Discount([
            'value'      => $value,
            'target'     => DiscountTarget::Items,
            'type'       => DiscountType::Value,
            'attributes' => $options,
        ]));

        $this->assertEquals(1, DiscountModel::query()->count());

        /** @var DiscountModel $storedDiscount */
        $storedDiscount = DiscountModel::query()->latest()->first();
        $this->assertTrue($storedDiscount->value->equals($value));
        $this->assertTrue($storedDiscount->target === DiscountTarget::Items);
        $this->assertTrue($storedDiscount->type === DiscountType::Value);
        $this->assertEquals(0, $options->diff($storedDiscount->discount_attributes)->count());
    }

    public static function driversProvider()
    {
        return [
            'session' => [
                'session',
                SessionStorage::class,
            ],
            'cache' => [
                'cache',
                CacheStorage::class,
            ],
            'eloquent' => [
                'eloquent',
                EloquentStorage::class,
            ],
        ];
    }
}
