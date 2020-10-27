<?php

namespace Weble\LaravelEcommerce\Payment;

abstract class PaymentState
{
    public const NEW        = 'new';
    public const PROCESSING = 'processing';
    public const COMPLETED  = 'completed';
    public const FAILED     = 'failed';
    public const CANCELED   = 'canceled';
    public const REFUNDED   = 'refunded';
}
