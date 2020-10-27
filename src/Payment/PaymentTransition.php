<?php

namespace Weble\LaravelEcommerce\Payment;

abstract class PaymentTransition
{
    public const PROCESS       = 'process';
    public const COMPLETE      = 'complete';
    public const FAIL          = 'fail';
    public const CANCEL        = 'cancel';
    public const REFUND        = 'refund';
}
