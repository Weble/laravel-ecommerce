<?php

namespace Weble\LaravelEcommerce\Tests\Cart;

use Weble\LaravelEcommerce\Cart\Cart;
use Weble\LaravelEcommerce\Cart\CartItemModel;
use Weble\LaravelEcommerce\Tests\mocks\Product;
use Weble\LaravelEcommerce\Tests\mocks\User;
use Weble\LaravelEcommerce\Tests\TestCase;

class MultipleCartsTest extends TestCase
{
    /**
     * @test
     */
    public function stores_different_carts_in_db()
    {
        config()->set('ecommerce.cart.instances.cart.storage', 'eloquent');
        config()->set('ecommerce.cart.instances.wishlist.storage', 'eloquent');

        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Product $product */
        $product         = factory(Product::class)->create(['price' => money(100)]);
        $productWishlist = factory(Product::class)->create(['price' => money(200)]);

        $this->actingAs($user);

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart')->instance('cart');
        $this->assertEquals('cart', $cart->instanceName());
        $cartItem = $cart->add($product, 2);

        /** @var CartItemModel $storedCartItem */
        $storedCartItem = CartItemModel::query()->latest('id')->first();
        $this->assertEquals('cart', $storedCartItem->instance);
        $this->assertEquals(2, $storedCartItem->quantity);
        $this->assertEquals($product->getKey(), $storedCartItem->product->getKey());
        $this->assertTrue($product->price->equals($storedCartItem->price));
        $this->assertEquals($user->id, $storedCartItem->user_id);

        /** @var Cart $cart */
        $cart     = app('ecommerce.cart')->instance('wishlist');
        $this->assertEquals('wishlist', $cart->instanceName());
        $cart->add($productWishlist, 3);

        /** @var CartItemModel $storedCartItem */
        $storedCartItemWishlist = CartItemModel::query()->latest('id')->first();
        $this->assertEquals('wishlist', $storedCartItemWishlist->instance);
        $this->assertEquals(3, $storedCartItemWishlist->quantity);
        $this->assertEquals($productWishlist->getKey(), $storedCartItemWishlist->product->getKey());
        $this->assertTrue($productWishlist->price->equals($storedCartItemWishlist->price));
        $this->assertEquals($user->id, $storedCartItemWishlist->user_id);

        $this->assertEquals(2, CartItemModel::query()->count());

        $this->assertEquals(2, app('ecommerce.cart')->instance('cart')->items()->total());
        $this->assertEquals(3, app('ecommerce.cart')->instance('wishlist')->items()->total());
    }
}
