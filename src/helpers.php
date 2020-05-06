<?php

if (! function_exists('currencyManager')) {
    /**
     * @return \Weble\LaravelEcommerce\Currency\CurrencyManager
     */
    function currencyManager()
    {
        return app('ecommerce.currencyManager');
    }
}
