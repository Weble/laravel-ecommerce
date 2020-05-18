<?php

namespace Weble\LaravelEcommerce\Support;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DTOCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        dd($attributes);
        return (new $class($value));
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $value->toJson();
    }
}
