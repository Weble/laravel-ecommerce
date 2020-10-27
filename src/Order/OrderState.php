<?php

namespace Weble\LaravelEcommerce\Order;

abstract class OrderState
{
    public const NEW       = 'new';
    public const PAYED     = 'payed';
    public const CANCELED  = 'canceled';
    public const REFUNDED  = 'refunded';
    public const COMPLETED = 'completed';
}
