<?php

namespace Weble\LaravelEcommerce\Cart;

use Illuminate\Support\Collection;

class CartItemCollection extends Collection
{
    public function total(): float
    {
        return $this->sum(function (CartItem $cartItem) {
            return $cartItem->quantity;
        });
    }
}
