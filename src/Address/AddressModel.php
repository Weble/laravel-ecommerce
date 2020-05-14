<?php

namespace Weble\LaravelEcommerce\Address;

use Illuminate\Database\Eloquent\Model;

class AddressModel extends Model
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.customer.table', 'coupons'));
    }
}
