<?php

namespace Weble\LaravelEcommerce\Discount;

use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Weble\LaravelEcommerce\Support\CurrencyCast;
use Weble\LaravelEcommerce\Support\DTOCast;

class DiscountModel extends Model
{
    protected $guarded = [];

    protected $casts = [
        'target'   => DTOCast::class . ':' . DiscountTarget::class,
        'type'     => DTOCast::class . ':' . DiscountType::class,
        'currency' => CurrencyCast::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.discounts', 'discounts'));
    }

    protected function getValueAttribute($value)
    {
        if (DiscountType::value()->equals($this->type)) {
            return new Money($value, $this->currency);
        }

        return $value;
    }

    public function toDTO(): Discount
    {
        return new Discount([
            'type'   => $this->type,
            'target' => $this->target,
            'value'  => $this->value,
        ]);
    }
}
