<?php

namespace Weble\LaravelEcommerce\Order;

use Weble\LaravelEcommerce\Contracts\StateInterface;

enum OrderState: string implements StateInterface
{
    case New       = 'new';
    case Payed     = 'payed';
    case Canceled  = 'canceled';
    case Refunded  = 'refunded';
    case Completed = 'completed';

    public function value(): string
    {
        return $this->value;
    }
}
