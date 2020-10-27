<?php


namespace Weble\LaravelEcommerce\Support;

use SM\Event\TransitionEvent;

class StateHistoryManager extends \Iben\Statable\Services\StateHistoryManager
{
    public function __invoke(TransitionEvent $event)
    {
        $this->storeHistory($event);
    }
}
