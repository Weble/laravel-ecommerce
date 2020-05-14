<?php

return [

    'currency' => [
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
        'default' => config('money.currency', config('app.currency', 'USD')),

        /*
        |--------------------------------------------------------------------------
        | Default User Currency
        |--------------------------------------------------------------------------
        |
        | This is the default currency used to print out money values to the user when
        | the user itself doesn't provide an alternative
        */
        'user' => config('ecommerce.currency', 'USD'),

        /*
        |--------------------------------------------------------------------------
        | Session Key
        |--------------------------------------------------------------------------
        |
        | This is the session key used by the system to store the active user's currency
        */
        'session_key' => 'ecommerce.currency',
    ],

    'customer' => [
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
        | Session Key
        |--------------------------------------------------------------------------
        |
        | This is the session key used by the system to store the active customer data
        */
        'session_key' => 'ecommerce.customer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Drivers
    |--------------------------------------------------------------------------
    |
    | List of available "Storage Drivers".
    |
    | When saving "temporary data", like carts, customer informations, addresses, etc
    | we use one of these "stores" to persist this data in some way.
    |
    | By default we provide "session", "cache" and "eloquent"
    */

    'storage' => [

        'stores' => [

            'session' => [
                'prefix' => 'ecommerce.',
            ],

            'cache' => [
                // this can be any cache driver you've registered within laravel.
                // "default" means the default driver used for everything else
                'driver' => 'default',
                'prefix' => 'ecommerce.',
                'session_key' => 'ecommerce.store.cache.',
            ],

            'eloquent' => [
                'fallback' => 'session',
                'session_key' => 'ecommerce.store.eloquent.',
                'models' => [
                    'items' => \Weble\LaravelEcommerce\Cart\CartItemModel::class,
                    'discounts' => \Weble\LaravelEcommerce\Discount\DiscountModel::class,
                    'customer' => \Weble\LaravelEcommerce\Customer\CustomerModel::class,
                    'address' => \Weble\LaravelEcommerce\Address\AddressModel::class,
                ],
            ],
        ],

        'default' => 'session',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart instances
    |--------------------------------------------------------------------------
    |
    | List of available "Cart Instances".
    | By default we provide the standard "Cart", plus a secondary one that you
    | can use for a wishlist.
    |
    | For each instance, you can specify the storage to use in order to
    | persist the data.
    |
    | You can use the same driver for multiple instances
    */

    'cart' => [
        'instances' => [
            'cart' => [
                'storage' => 'session',
            ],

            /*
            'cart' => [
                'storage' => 'eloquent',
            ],
            */

            'wishlist' => [
                'storage' => 'session',
                // Any other option will be passes through to the store driver
                'prefix' => 'wishlist_',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Default cart instance
        |--------------------------------------------------------------------------
        |
        | When you add something to the cart, which instance gets selected by default
        */
        'default' => 'cart',
    ],

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    |
    | Details of the store selling the products.
    */
    'store' => [
        'address' => [
            'country' => 'IT',
            'city' => 'Vicenza',
            'zip' => '36100',
            'state' => 'VI',
            'address' => 'Via Enrico Fermi, 265',
            'address2' => '',
            'organization' => 'Weble Srl',
            'vat_id' => '03579410246',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Taxes
    |--------------------------------------------------------------------------
    |
    | Tax settings
    */

    'tax' => [
        /*
        |--------------------------------------------------------------------------
        | Address to Use for Taxes
        |--------------------------------------------------------------------------
        |
        | Which address should be used when calculating taxes. "shipping" or "billing"
        */
        'address_type' => (string)\Weble\LaravelEcommerce\Address\AddressType::shipping(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Coupon
    |--------------------------------------------------------------------------
    |
    | Coupon table name
    */
    'coupon' => [
        'table' => 'coupons',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurable Classes
    |--------------------------------------------------------------------------
    |
    | You can swap our classes with yours here
    */
    'classes' => [
        'storageManager' => \Weble\LaravelEcommerce\Storage\StorageManager::class,
        'currencyManager' => \Weble\LaravelEcommerce\Currency\CurrencyManager::class,
        'taxManager' => \Weble\LaravelEcommerce\Tax\TaxManager::class,
        'cartManager' => \Weble\LaravelEcommerce\Cart\CartManager::class,
        'cartItemModel' => \Weble\LaravelEcommerce\Cart\CartItemModel::class,
        'cart' => \Weble\LaravelEcommerce\Cart\Cart::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurable Table names
    |--------------------------------------------------------------------------
    |
    | When using the eloquent storage, by default we'll use these table names
    */
    'tables' => [
        'items' => 'cart_items',
    ],
];
