<?php

namespace Weble\LaravelEcommerce\Tax;

use Cknow\Money\Money;
use CommerceGuys\Addressing\AddressInterface;
use CommerceGuys\Tax\Model\TaxRateAmount;
use CommerceGuys\Tax\Resolver\Context;
use CommerceGuys\Tax\Resolver\TaxResolver;
use CommerceGuys\Tax\Resolver\TaxResolverInterface;
use Illuminate\Contracts\Foundation\Application;
use Weble\LaravelEcommerce\Address\StoreAddress;
use Weble\LaravelEcommerce\Purchasable;

class TaxManager
{
    protected TaxResolverInterface $taxResolver;

    public function __construct(Application $app)
    {
        $this->taxResolver = $app->make(TaxResolverInterface::class);
    }

    public function resolver(): TaxResolverInterface
    {
        return $this->taxResolver;
    }

    public function taxFor(Purchasable $product, ?AddressInterface $address = null): Money
    {
        $storeAddress = new StoreAddress();
        if ($address === null) {
            $address = $storeAddress;
        }

        $currency = $product->cartPrice()->getMoney()->getCurrency();
        $context = new Context($address, $storeAddress);

        /** @var TaxRateAmount[] $amounts */
        $amounts = app()->make(TaxResolver::class)->resolveAmounts($product, $context);

        if (count($amounts) <= 0) {
            return new Money(0, $currency);
        }

        $amount = array_shift($amounts)->getAmount();

        return $product->cartPrice()->multiply($amount);
    }
}
