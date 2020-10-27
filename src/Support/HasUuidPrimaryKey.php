<?php

namespace Weble\LaravelEcommerce\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** @mixin Model */
trait HasUuidPrimaryKey
{
    public static function bootHasUuidPrimaryKey()
    {
        static::creating(function (Model $model) {
            $model->generateUniqueId();
        });
    }

    protected function generateUniqueId(): void
    {
        if ($this->getKey() !== null) {
            return;
        }

        $this->{$this->getKeyName()} = Str::orderedUuid();
    }
}
