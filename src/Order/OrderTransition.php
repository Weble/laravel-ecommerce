<?php

namespace Weble\LaravelEcommerce\Order;

abstract class OrderTransition
{
    public const PAY       = 'pay';
    public const CANCEL    = 'cancel';
    public const REFUND    = 'refund';
    public const COMPLETE  = 'complete';
}
