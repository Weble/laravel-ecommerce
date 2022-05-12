<?php

namespace Weble\LaravelEcommerce\Payment;

use Weble\LaravelEcommerce\Contracts\TransitionInterface;

enum PaymentTransition: string implements TransitionInterface
{
    case Process       = 'process';
    case Complete      = 'complete';
    case Fail          = 'fail';
    case Cancel        = 'cancel';
    case Refund        = 'refund';

    public function value(): string
    {
        return $this->value;
    }

    public function name(): string
    {
        return ucfirst($this->value());
    }
}
