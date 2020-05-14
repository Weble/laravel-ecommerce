<?php

namespace Weble\LaravelEcommerce\Customer;

use Illuminate\Database\Eloquent\Model;

class CustomerModel extends Model
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.customer.table', 'coupons'));
    }
}
