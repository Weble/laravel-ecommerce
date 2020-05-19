<?php

namespace Weble\LaravelEcommerce\Order\Concern;

use Iben\Statable\Statable;

trait InteractsWithStateMachine
{
    use Statable;

    protected function getGraph()
    {
        return 'ecommerce-order';
    }

    protected function saveBeforeTransition()
    {
        return true;
    }
}
