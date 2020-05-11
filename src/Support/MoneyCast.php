<?php

namespace Weble\LaravelEcommerce\Support;

use Cknow\Money\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Money\Currency;

class MoneyCast implements CastsAttributes
{
    private ?string $currencyCode;
    private ?string $currencyField;
    private Currency $currency;

    public function __construct(?string $currencyCode = null, ?string $currencyField = null)
    {
        $this->currencyCode = $currencyCode;
        $this->currencyField = $currencyField;
        $this->currency = currencyManager()->defaultCurrency();

        if ($this->currencyCode) {
            $this->currency = currencyManager()->currency($this->currencyCode);
        }
    }

    /**
     * Cast the given value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return Money
     */
    public function get($model, $key, $value, $attributes)
    {
        if ($this->currencyField) {
            return currencyManager()->convert($value, $model->{$this->currencyField} ?: null);
        }

        $value = new Money($value, $this->currency);

        return currencyManager()->convert($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param string|null|Money $value
     * @param array $attributes
     * @return string|array
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Money) {
            return $value;
        }

        $storeAsCurrency = $this->currencyCode;
        if (!$storeAsCurrency) {
            $storeAsCurrency = $value->getCurrency();
        }

        if (!$storeAsCurrency instanceof Currency) {
            $storeAsCurrency = currencyManager()->currency($storeAsCurrency);
        }

        $money = currencyManager()->convert($value, $storeAsCurrency)->getMoney()->getAmount();

        if ($this->currencyField) {
            return [
                $this->currencyField => $storeAsCurrency,
                $key                 => $money,
            ];
        }

        return $money;
    }
}
