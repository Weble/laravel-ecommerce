<?php

namespace Weble\LaravelEcommerce\Discount;

use Illuminate\Database\Eloquent\Model;

class DiscountModel extends Model
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.coupon.table', 'coupons'));
    }
}
