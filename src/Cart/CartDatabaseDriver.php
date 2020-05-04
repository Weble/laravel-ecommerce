<?php


namespace Weble\LaravelEcommerce\Cart;

class CartDatabaseDriver implements CartDriverInterface
{
    protected $prefix;

    public function __construct(array $config = [])
    {
        $this->prefix = $config['session_key_prefix'] ?? 'cart_';
    }
}
