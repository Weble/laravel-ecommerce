<?php

namespace Weble\LaravelEcommerce\Facades;

use Illuminate\Support\Facades\Facade;

class CurrencyFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ecommerce.currencyManager';
    }
}
