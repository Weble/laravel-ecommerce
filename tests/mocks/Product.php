<?php

namespace Weble\LaravelEcommerce\Tests\mocks;

use Cknow\Money\Money;
use Cknow\Money\MoneyCast;
use CommerceGuys\Tax\Model\TaxTypeInterface;
use CommerceGuys\Tax\Repository\TaxTypeRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Purchasable;

class Product extends Model implements Purchasable
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'price' => MoneyCast::class,
    ];

    public function cartTaxType(): TaxTypeInterface
    {
        return (new TaxTypeRepository())->get('it_vat_standard');
    }

    public function isPhysical()
    {
        return true;
    }

    public function cartPrice(?Collection $cartAttributes = null): Money
    {
        return $this->price;
    }
}
