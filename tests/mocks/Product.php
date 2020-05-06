<?php


namespace Weble\LaravelEcommerce\Tests\mocks;

use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Purchasable;

class Product implements Purchasable
{
    private int $id;
    private Money $price;

    public function __construct(int $id, Money $price)
    {
        $this->id = $id;
        $this->price = $price;
    }

    public function cartId()
    {
        return $this->id;
    }

    public function cartPrice(?Collection $cartAttributes = null): Money
    {
        return $this->price;
    }
}
