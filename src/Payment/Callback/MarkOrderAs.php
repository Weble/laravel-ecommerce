<?php

namespace Weble\LaravelEcommerce\Payment\Callback;

use SM\Event\TransitionEvent;
use Weble\LaravelEcommerce\Payment\Payment;

class MarkOrderAs
{
    protected string $transition;

    public function __construct(string $transition)
    {
        $this->transition = $transition;
    }

    public function __invoke(TransitionEvent $event)
    {
        $sm = $event->getStateMachine();

        /** @var Payment $payment */
        $payment = $sm->getObject();
        $payment->order->apply($this->transition);
    }
}
