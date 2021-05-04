<?php


namespace Weble\LaravelEcommerce\Storage;


use Illuminate\Database\Eloquent\Builder;

interface StoresDifferentInstances
{
    public function scopeForInstance(Builder $query, string $instanceName): Builder;
}
