<?php

namespace Weble\LaravelEcommerce\Tax;

use Cknow\Money\Money;
use CommerceGuys\Addressing\AddressInterface;
use CommerceGuys\Tax\Model\TaxRateAmount;
use CommerceGuys\Tax\Resolver\Context;
use CommerceGuys\Tax\Resolver\TaxResolver;
use CommerceGuys\Tax\Resolver\TaxResolverInterface;
use CommerceGuys\Tax\TaxableInterface;
use Exception;
use Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException;
use Mpociot\VatCalculator\VatCalculator;
use Weble\LaravelEcommerce\Address\Address;
use Weble\LaravelEcommerce\Address\StoreAddress;
use Weble\LaravelEcommerce\Purchasable;

class TaxManager
{
    protected TaxResolverInterface $taxResolver;
    protected VatCalculator $vatCalculator;
    protected StoreAddress $storeAddress;

    public function __construct(TaxResolverInterface $taxResolver, VatCalculator $vatCalculator)
    {
        $this->vatCalculator = $vatCalculator;
        $this->taxResolver = $taxResolver;
        $this->storeAddress = new StoreAddress();

        $this->vatCalculator->setBusinessCountryCode($this->storeAddress->getCountryCode());
    }

    public function resolver(): TaxResolverInterface
    {
        return $this->taxResolver;
    }

    public function taxFor(Purchasable|TaxableInterface $product, ?Money $price = null, ?AddressInterface $address = null): Money
    {

        if ($address === null) {
            $address = $this->storeAddress;
        }

        if ($product instanceof Purchasable && $price === null) {
            $price = $product->cartPrice();
        }

        if ($price === null) {
            throw new Exception("Price cannot be null when calculating taxes");
        }

        if ($this->vatCalculator->shouldCollectVAT($address->getCountryCode())) {
            return $this->vatFor($price, $address);
        }

        return $this->genericTaxFor($price, $address, $product);
    }

    public function vatFor(Money $price, AddressInterface $address, ?string $vatId = null): Money
    {
        $isCompany = $this->isValidEUCompany($address, $vatId);

        $this->vatCalculator->calculate($price->getAmount(), $address->getCountryCode(), $address->getPostalCode(), $isCompany);

        return new Money((string) $this->vatCalculator->getTaxValue(), $price->getCurrency());
    }

    public function genericTaxFor(Money $price, ?AddressInterface $address, TaxableInterface|Purchasable $product): Money
    {
        $currency = $price->getMoney()->getCurrency();
        $context = new Context($address, $this->storeAddress);

        /** @var TaxRateAmount[] $amounts */
        $amounts = $this->taxResolver->resolveAmounts($product, $context);

        if (count($amounts) <= 0) {
            return new Money(0, $currency);
        }

        $amount = array_shift($amounts)->getAmount();

        /** @var Money $tax */
        $tax =  $price->multiply((string)$amount);

        return $tax;
    }

    private function isValidEUCompany(AddressInterface $address): bool
    {
        if (!$address->getOrganization()) {
            return false;
        }

        if (!$address instanceof Address) {
            return false;
        }

        $vatId = $address->getVatId();

        if (!$vatId) {
            return false;
        }

        $shouldCheckVatId = config('ecommerce.tax.vat_id_check', true);
        if (!$shouldCheckVatId) {
            return true;
        }

        try {
            return $this->vatCalculator->isValidVATNumber($vatId);
        } catch (VATCheckUnavailableException) {
            return false;
        }
    }
}
