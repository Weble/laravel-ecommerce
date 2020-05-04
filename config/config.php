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
    'currencies'     => \Money\Currencies\ISOCurrencies::class,

    /*
    |--------------------------------------------------------------------------
    | Default Currency Code
    |--------------------------------------------------------------------------
    |
    | Falls back to \Cknow\Money config.
    | Needs to be in the list of the available currencies above.
    */
    'currency'       => config('money.currency', config('app.currency', 'USD')),

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | Falls back to \Cknow\Money config.
    | Used for formatting prices.
    */
    'locale'         => config('money.locale', config('app.locale', 'en_US')),

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
        'cart'     => [
            'driver'             => 'session',
            'session_key_prefix' => 'cart_', // This is specific for some drivers
            'multiple'           => false
        ],
        'wishlist' => [
            'driver'   => 'session',
            'session_key_prefix' => 'wishlist_', // This is specific for some drivers
            'multiple' => true
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Default cart instance
    |--------------------------------------------------------------------------
    |
    | When you add something to the cart, which instance gets selected by default
    */
    'default_cart_instance' => 'cart'
];
