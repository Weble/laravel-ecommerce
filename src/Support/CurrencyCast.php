<?php

namespace Weble\LaravelEcommerce\Support;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Money\Currency;

class CurrencyCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return Currency|null
     */
    public function get($model, $key, $value, $attributes)
    {
        if ($value === null) {
            return null;
        }

        if (! $value instanceof Currency) {
            $value = new Currency($value);
        }

        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param string|null|Currency $value
     * @param array $attributes
     * @return string|null
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value === null) {
            return null;
        }

        if (! $value instanceof Currency) {
            $value = new Currency($value);
        }

        return $value->getCode();
    }
}
