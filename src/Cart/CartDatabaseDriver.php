<?php

namespace Weble\LaravelEcommerce\Cart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class CartDatabaseDriver extends CartDriver implements CartDriverInterface
{
    /**
     * @var Model|CartItemModel
     */
    protected Model $model;

    protected string $uuid;

    public function __construct(string $instanceName, array $config = [])
    {
        parent::__construct($instanceName, $config);

        $this->model = app()->make(config('ecommerce.classes.cartItemModel', CartItemModel::class));

        $sessionKey = $config['session_key'] ?? 'ecommerce.cart_id';
        $this->uuid = session()->get($sessionKey, Str::uuid());

        $this->model->whereUuid($this->uuid);
    }

    public function set(CartItem $cartItem): CartDriverInterface
    {
        $class = get_class($this->model);
        $item = $class::fromCartItem($cartItem)->fill([
            'instance' => $this->instanceName(),
        ]);

        $item->save();

        return $this;
    }

    public function get(string $cartItemId): CartItem
    {
        return $this->model->findOrFail($cartItemId);
    }

    public function has(string $cartItemId): bool
    {
        try {
            $this->model->findOrFail($cartItemId);

            return true;
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    public function remove(CartItem $cartItem): CartDriverInterface
    {
        return $this->model->destroy($cartItem->getId());
    }

    public function clear(): CartDriverInterface
    {
        $this->model->delete();

        return $this;
    }

    public function items(): CartItemCollection
    {
        return CartItemCollection::make($this->model->get()->map(function (CartItemModel $model) {
            return $model->toCartItem();
        }));
    }
}
