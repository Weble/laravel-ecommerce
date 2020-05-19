<?php

namespace Weble\LaravelEcommerce\Order\Concern;

use Iben\Statable\Statable;
use Weble\LaravelEcommerce\Order\OrderHistory;

trait InteractsWithStateMachine
{
    use Statable;

    public function stateHistory()
    {
        return $this->hasMany(config('ecommerce.classes.orderHistoryModel', OrderHistory::class));
    }

    protected function getGraph()
    {
        return 'ecommerce-order';
    }

    protected function saveBeforeTransition()
    {
        return true;
    }
}
