<?php

namespace Weble\LaravelEcommerce\Payment;

use Weble\LaravelEcommerce\Contracts\StateInterface;

enum PaymentState: string implements StateInterface
{
    case Created    = 'created';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Failed     = 'failed';
    case Canceled   = 'canceled';
    case Refunded   = 'refunded';

    public function value(): string
    {
        return $this->value;
    }


}
