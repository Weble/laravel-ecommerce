<?php

namespace Weble\LaravelEcommerce\Order\Concern;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Weble\LaravelEcommerce\Order\Order;
use Weble\LaravelEcommerce\Payment\Payment;
use Weble\LaravelEcommerce\Payment\PaymentState;

/**
 * @mixin Order
 */
trait Payable
{
    public function payments(): HasMany
    {
        return $this->hasMany(config('ecommerce.classes.paymentModel', Payment::class));
    }

    public function createPayment(): Payment
    {
        return $this->payments()->create([
            'total' => $this->total,
            'payment_gateway' => $this->payment_gateway,
            'state' => PaymentState::NEW,
            'currency' => $this->currency
        ]);
    }
}
