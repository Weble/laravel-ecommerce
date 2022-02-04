<?php

namespace Weble\LaravelEcommerce\Payment;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Weble\LaravelEcommerce\Customer\Customer;
use Weble\LaravelEcommerce\Order\Order;
use Weble\LaravelEcommerce\Support\CurrencyCast;
use Weble\LaravelEcommerce\Support\DTOCast;
use Weble\LaravelEcommerce\Support\InteractsWithStateMachine;

class Payment extends Model
{
    use InteractsWithStateMachine;

    protected $guarded = [];

    protected $casts = [
        'customer'           => DTOCast::class . ':' . Customer::class,
        'currency'           => CurrencyCast::class,
        'discounts'          => 'collection',
        'discounts_subtotal' => MoneyIntegerCast::class . ':currency',
        'items_subtotal'     => MoneyIntegerCast::class . ':currency',
        'items_total'        => MoneyIntegerCast::class . ':currency',
        'subtotal'           => MoneyIntegerCast::class . ':currency',
        'tax'                => MoneyIntegerCast::class . ':currency',
        'total'              => MoneyIntegerCast::class . ':currency',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('ecommerce.tables.payments', 'payments'));
    }

    protected function getGraph(): string
    {
        return config('ecommerce.payment.workflow.graph', 'ecommerce-payment');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.classes.orderModel', Order::class));
    }
}
