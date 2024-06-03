<?php

namespace Weble\LaravelEcommerce\Storage;

use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelData\Data;

/**
 * @method Builder forCurrentUser()
 * @mixin \Eloquent
 */
interface StoresEcommerceData
{
    public function scopeForCurrentUser(Builder $query): Builder;

    public function fromCartValue($value, string $key, string $instanceName): self;

    public function toCartValue(): Data;
}
