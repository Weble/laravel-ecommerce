<?php

namespace Weble\LaravelEcommerce\Support;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DTOCast implements CastsAttributes
{
    protected string $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function get($model, string $key, $value, array $attributes)
    {
        return (new $this->class(json_decode($value, true)));
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $value->toJson();
    }
}
