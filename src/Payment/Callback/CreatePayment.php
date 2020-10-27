<?php

namespace Weble\LaravelEcommerce\Payment\Callback;

use SM\Event\TransitionEvent;
use Weble\LaravelEcommerce\Order\Order;

class CreatePayment
{
    public function __invoke(TransitionEvent $event)
    {
        $sm = $event->getStateMachine();

        /** @var Order $model */
        $order = $sm->getObject();
        $order->createPayment();
    }
}
