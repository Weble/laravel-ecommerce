<?php

namespace Weble\LaravelEcommerce\Tests\Tax;

use Cknow\Money\Money;
use CommerceGuys\Addressing\Address;
use CommerceGuys\Tax\Model\TaxRateAmount;
use CommerceGuys\Tax\Resolver\Context;
use CommerceGuys\Tax\Resolver\TaxResolver;
use CommerceGuys\Tax\Resolver\TaxResolverInterface;
use Money\Currency;
use Weble\LaravelEcommerce\Tax\TaxManager;
use Weble\LaravelEcommerce\Tests\mocks\Product;
use Weble\LaravelEcommerce\Tests\TestCase;

class TaxTest extends TestCase
{
    /** @test */
    public function can_get_tax_resolver()
    {
        $this->assertInstanceOf(TaxResolverInterface::class, app('ecommerce.tax.resolver'));
    }

    /** @test */
    public function can_get_tax_manager()
    {
        $this->assertInstanceOf(TaxManager::class, app('ecommerce.tax'));
    }

    /** @test */
    public function can_calculate_tax_for_physical_product()
    {
        // This has IT stadard rate type (22%) and it's a physical product
        $product = new Product([
            'price' => money(100),
        ]);

        $customerAddress = (new Address())
            ->withCountryCode('IT');

        $storeAddress = (new Address())
            ->withCountryCode('IT');

        $context = new Context($customerAddress, $storeAddress);

        /** @var TaxRateAmount[] $amounts */
        $amounts = app()->make(TaxResolver::class)->resolveAmounts($product, $context);
        $tax     = $amounts[0]->getAmount();

        $tax = money($tax * 100);
        $this->assertTrue(money(22)->equals($tax));
    }

    /** @test */
    public function can_calculate_tax_for_physical_product_with_different_country_eu_private_customer()
    {
        // Disable vat vies check in test
        config()->set('ecommerce.tax.vat_id_check', false);

        // This has IT stadard rate type (22%) and it's a physical product
        $product = new Product([
            'price' => money(100),
        ]);

        $customerAddress = (new Address())
            ->withCountryCode('DE');

        $tax = \taxManager()->taxFor($product, $product->cartPrice(), $customerAddress);

        // Germany should be 19% for private customer
        $this->assertTrue((new Money(19, new Currency($product->cartPrice()->getCurrency())))->equals($tax));
    }

    /** @test */
    public function can_calculate_tax_for_physical_product_with_different_country_eu_company()
    {
        // Disable vat vies check in test
        config()->set('ecommerce.tax.vat_id_check', false);

        // This has IT stadard rate type (22%) and it's a physical product
        $product = new Product([
            'price' => money(100),
        ]);

        $customerAddress = (new \Weble\LaravelEcommerce\Address\Address([
            'company' => 'YOOtheme Gmbh',
            'country' => 'DE',
            'vatId' => 'DEVFCLQFW054',
        ]));

        $tax = \taxManager()->taxFor($product, $product->cartPrice(), $customerAddress);

        // Germany should be 0 for valid eu company
        $this->assertTrue((new Money(0, new Currency($product->cartPrice()->getCurrency())))->equals($tax));
    }
}
