<?php

namespace Weble\LaravelEcommerce\Currency;

use Cknow\Money\Money;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Money\Converter;
use Money\Currencies;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exception\UnknownCurrencyException;
use Money\Exchange;
use Money\Exchange\ReversedCurrenciesExchange;
use Money\Exchange\SwapExchange;
use Swap\Swap;

class CurrencyManager
{
    protected Currency $defaultCurrency;
    protected Currency $userCurrency;
    protected Currencies $availableCurrencies;
    protected Exchange $exchange;
    protected Converter $converter;
    protected Collection $availableCurrenciesCollection;

    public function __construct(Application $app)
    {
        $this->setupCurrencies();
        $this->setupCurrencyConversion(app()->make(Swap::class));
    }

    public function fromFloat(float $amount, Currency $currency): Money
    {
        $intValue = $amount * pow(10, $this->availableCurrencies()->subunitFor($currency));

        return new Money((int) $intValue, $currency);
    }

    public function convert(Money $money, ?Currency $counterCurrency = null, $roundingMode = \Money\Money::ROUND_HALF_UP): Money
    {
        if ($counterCurrency === null) {
            $counterCurrency = $this->userCurrency();
        }

        return Money::convert($this->converter->convert($money->getMoney(), $counterCurrency, $roundingMode));
    }

    public function setUserCurrency(Currency $currency): self
    {
        if (! $this->isActiveCurrency($currency)) {
            $currency = $this->defaultCurrency();
        }
        $this->userCurrency = $currency;

        return $this;
    }

    public function userCurrency(): Currency
    {
        return $this->userCurrency ?: $this->defaultCurrency();
    }

    public function isActiveCurrency($currency): bool
    {
        if (! $currency instanceof Currency) {
            $currency = new Currency(strtoupper($currency));
        }

        if (! $this->availableCurrencies()->contains($currency)) {
            return false;
        }

        return true;
    }

    public function currency(string $code): Currency
    {
        foreach ($this->availableCurrencies() as $currency) {
            if (strtolower($currency->getCode()) === strtolower($code)) {
                return $currency;
            }
        }

        throw new UnknownCurrencyException($code);
    }

    public function availableCurrencies(): Currencies
    {
        return $this->availableCurrencies;
    }

    public function availableCurrenciesCollection(): Collection
    {
        return $this->availableCurrenciesCollection;
    }

    public function defaultCurrency(): Currency
    {
        return $this->defaultCurrency;
    }

    protected function setupCurrencies(): void
    {
        $currencyListClass = config('ecommerce.currency.currencies', ISOCurrencies::class);

        if (! class_exists($currencyListClass)) {
            $currencyListClass = ISOCurrencies::class;
        }

        $this->availableCurrencies = app($currencyListClass);
        $this->availableCurrenciesCollection = Collection::make($this->availableCurrencies);
        Money::setCurrencies($this->availableCurrencies);

        $this->defaultCurrency = $this->currency(config('ecommerce.currency.default', config('money.user_default', 'EUR')));
        $this->userCurrency = $this->currency(config('ecommerce.currency.user', 'USD'));
    }

    protected function setupCurrencyConversion(Swap $swap): void
    {
        $this->exchange = new ReversedCurrenciesExchange(
            new SwapExchange($swap)
        );
        $this->converter = new Converter($this->availableCurrencies(), $this->exchange);
    }
}
