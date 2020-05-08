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

if (! function_exists('cartManager')) {
    /**
     * @return \Weble\LaravelEcommerce\Cart\CartManager
     */
    function cartManager()
    {
        return app('ecommerce.cartManager');
    }
}

if (! function_exists('taxManager')) {
    /**
     * @return \Weble\LaravelEcommerce\Tax\TaxManager
     */
    function taxManager()
    {
        return app('ecommerce.taxManager');
    }
}
