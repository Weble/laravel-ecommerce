<?php

namespace Weble\LaravelEcommerce\Storage;

use Illuminate\Database\Eloquent\Builder;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * @method self withCartKey(Builder $query, string $key)
 */
interface StoresEcommerceData
{
    public function scopeWithCartKey(Builder $query, string $key): self;

    public function fromCartValue($value, string $key, string $instanceName): self;

    public function toCartValue(): DataTransferObject;
}
