<?php

namespace Weble\LaravelEcommerce\Payment;

use Illuminate\Database\Eloquent\Model;
use Weble\LaravelEcommerce\Customer\Customer;
use Weble\LaravelEcommerce\Order\Concern\InteractsWithStateMachine;
use Weble\LaravelEcommerce\Support\CurrencyCast;
use Weble\LaravelEcommerce\Support\DTOCast;
use Cknow\Money\MoneyCast;

class Payment extends Model
{
    use InteractsWithStateMachine;

    protected $guarded = [];

    protected $casts = [
        'customer'           => DTOCast::class . ':' . Customer::class,
        'currency'           => CurrencyCast::class,
        'discounts'          => 'collection',
        'discounts_subtotal' => MoneyCast::class . ':currency',
        'items_subtotal'     => MoneyCast::class . ':currency',
        'items_total'        => MoneyCast::class . ':currency',
        'subtotal'           => MoneyCast::class . ':currency',
        'tax'                => MoneyCast::class . ':currency',
        'total'              => MoneyCast::class . ':currency',
    ];

    public $incrementing = false;
    protected $keyType   = 'uuid';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.payments', 'payments'));
    }
}
