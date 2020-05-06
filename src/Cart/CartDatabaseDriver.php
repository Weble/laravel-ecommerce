<?php


namespace Weble\LaravelEcommerce\Cart;

use Illuminate\Database\Eloquent\Model;
use Weble\LaravelEcommerce\Cart\Model\CartItemModel;

class CartDatabaseDriver extends CartDriver implements CartDriverInterface
{
    /**
     * @var Model|CartItemModel
     */
    protected Model $model;

    public function __construct(string $instanceName, array $config = [])
    {
        parent::__construct($instanceName, $config);

        $this->model = app(config('classes.cartItemModel'));
    }

    public function set(CartItem $cartItem): CartDriverInterface
    {
    }

    public function get(CartItem $cartItem): CartItem
    {
        // TODO: Implement get() method.
    }

    public function has(CartItem $cartItem): bool
    {
        // TODO: Implement has() method.
    }

    public function remove(CartItem $cartItem): CartDriverInterface
    {
        // TODO: Implement remove() method.
    }

    public function clear(): CartDriverInterface
    {
        // TODO: Implement clear() method.
    }

    public function items(): CartItemCollection
    {
        // TODO: Implement items() method.
    }
}
