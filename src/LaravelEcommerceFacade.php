<?php

namespace Weble\LaravelEcommerce;

use Illuminate\Support\Facades\Facade;

class LaravelEcommerceFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ecommerce';
    }
}
