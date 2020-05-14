<?php

if (! function_exists('currencyManager')) {
    /**
     * @return \Weble\LaravelEcommerce\Currency\CurrencyManager
     */
    function currencyManager()
    {
        return app('ecommerce.currency');
    }
}

if (! function_exists('cartManager')) {
    /**
     * @return \Weble\LaravelEcommerce\Cart\CartManager
     */
    function cartManager()
    {
        return app('ecommerce.cart');
    }
}

if (! function_exists('taxManager')) {
    /**
     * @return \Weble\LaravelEcommerce\Tax\TaxManager
     */
    function taxManager()
    {
        return app('ecommerce.tax');
    }
}


if (! function_exists('storageManager')) {
    /**
     * @return \Weble\LaravelEcommerce\Storage\StorageManager
     */
    function storageManager()
    {
        return app('ecommerce.storage');
    }
}
