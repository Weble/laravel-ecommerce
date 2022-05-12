<?php

namespace Weble\LaravelEcommerce\Order;

use Weble\LaravelEcommerce\Contracts\TransitionInterface;

enum OrderTransition: string implements TransitionInterface
{
    case Pay       = 'pay';
    case Cancel    = 'cancel';
    case Refund    = 'refund';
    case Complete  = 'complete';

    public function value(): string
    {
        return $this->value;
    }

    public function name(): string
    {
        return ucfirst($this->value());
    }
}
