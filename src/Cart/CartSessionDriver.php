<?php


namespace Weble\LaravelEcommerce\Cart;

use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;

class CartSessionDriver extends CartDriver implements CartDriverInterface
{
    protected string $prefix;
    protected string $instanceName;
    protected SessionManager $session;

    public function __construct(string $instanceName, array $config = [])
    {
        parent::__construct($instanceName, $config);

        $this->prefix = $config['session_key_prefix'] ?? 'cart_';
        $this->session = session();
    }

    public function set(CartItem $cartItem): CartDriverInterface
    {
        $items = $this->items();
        $items->put($cartItem->getId(), $cartItem);

        return $this->setItems($items);
    }

    public function get(CartItem $cartItem): CartItem
    {
        return $this->items()->get($cartItem->getId());
    }

    public function has(CartItem $cartItem): bool
    {
        return $this->items()->has($cartItem->getId());
    }

    public function remove(CartItem $cartItem): CartDriverInterface
    {
        $items = $this->items()->except($cartItem->getId());

        return $this->setItems($items);
    }

    public function clear(): CartDriverInterface
    {
        $this->session->remove($this->prefix . 'items');

        return $this;
    }

    public function items(): CartItemCollection
    {
        return CartItemCollection::make($this->session->get($this->prefix . 'items', []));
    }

    protected function setItems(Collection $items): self
    {
        $this->session->put($this->prefix . 'items', $items);

        return $this;
    }
}
