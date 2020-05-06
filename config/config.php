<?php

return [
    /*
    |--------------------------------------------------------------------------
    | List of available currencies
    |--------------------------------------------------------------------------
    |
    | We use MoneyPHP CurrencyList classes to determine the available currencies
    | for Laravel Ecommerce. By default all the ISO Currencies are available,
    | but feel free to implement your own. It needs to implement the \Money\Currencies
    | interface. Check http://moneyphp.org/en/stable/features/currencies.html#currencylist
    | for more info.
    */
    'currencies' => \Money\Currencies\ISOCurrencies::class,

    /*
    |--------------------------------------------------------------------------
    | Default Currency Code
    |--------------------------------------------------------------------------
    |
    | This is the default currency. It's what will be used to store money values
    | for the models where the currency is not stored alongside with it.
    | It's also the currency that will be used everytime you don't specify
    | a currency yourself when creating a money object.
    | Falls back to \Cknow\Money config.
    | Needs to be in the list of the available currencies above.
    */
    'currency' => config('money.currency', config('app.currency', 'USD')),

    /*
    |--------------------------------------------------------------------------
    | Default User Currency
    |--------------------------------------------------------------------------
    |
    | This is the default currency used to print out money values to the user when
    | the user itself doesn't provide an alternative
    */
    'user_currency' => config('ecommerce.currency', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | Falls back to \Cknow\Money config.
    | Used for formatting prices.
    */
    'locale' => config('money.locale', config('app.locale', 'en_US')),

    /*
    |--------------------------------------------------------------------------
    | Cart instances
    |--------------------------------------------------------------------------
    |
    | List of available "Cart Instances".
    | By default we provide the standard "Cart", plus a secondary one that you
    | can use for a wishlist.
    | You can also specify if an instance holds multiple instances, or not,
    | For example, if you want to have N wishlists per user.
    */
    'cart_instances' => [
        'cart' => [
            'driver' => 'session',
            'session_key_prefix' => 'ecommerce.cart_',
            // This is specific for some drivers
            'multiple' => false,
        ],

        /*
        'cart' => [
            'driver' => 'database',
            'session_key' => 'ecommerce.cart_id',
            // This is specific for some drivers
            'multiple' => false,
        ],
        */

        'wishlist' => [
            'driver' => 'session',
            'session_key_prefix' => 'wishlist_',
            // This is specific for some drivers
            'multiple' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default cart instance
    |--------------------------------------------------------------------------
    |
    | When you add something to the cart, which instance gets selected by default
    */
    'default_cart_instance' => 'cart',

    /*
    |--------------------------------------------------------------------------
    | Cart Database Table
    |--------------------------------------------------------------------------
    |
    | When using che CartDatabaseDriver, which table name will be used to
    | store the cart contents. Related to the CartItemModel class config
    */
    'cart_table' => 'cart_items',

    /*
    |--------------------------------------------------------------------------
    | Configurable Classes
    |--------------------------------------------------------------------------
    |
    | You can swap our classes with yours here
    */
    'classes' => [
        'currencyManager' => \Weble\LaravelEcommerce\Currency\CurrencyManager::class,
        'cartManager' => \Weble\LaravelEcommerce\Cart\CartManager::class,
        'cartItemModel' => \Weble\LaravelEcommerce\Cart\Model\CartItemModel::class,
        'cart' => \Weble\LaravelEcommerce\Cart\Cart::class,
    ],
];
