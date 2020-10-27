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
        'currencies'  => \Money\Currencies\ISOCurrencies::class,

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
        'default'     => config('money.defaultCurrency', config('app.currency', 'USD')),

        /*
        |--------------------------------------------------------------------------
        | Default User Currency
        |--------------------------------------------------------------------------
        |
        | This is the default currency used to print out money values to the user when
        | the user itself doesn't provide an alternative
        */
        'user'        => config('ecommerce.currency', 'USD'),

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
        'locale'      => config('money.locale', config('app.locale', 'en_US')),

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
                'driver'      => 'default',
                'prefix'      => 'ecommerce.',
                'session_key' => 'ecommerce.store.cache.',
            ],

            'eloquent' => [
                'fallback'    => 'session',
                'session_key' => 'ecommerce.store.eloquent.',
                'models'      => [
                    'items'    => \Weble\LaravelEcommerce\Cart\CartItemModel::class,
                    'customer' => \Weble\LaravelEcommerce\Customer\CustomerModel::class,
                    /*'discounts' => \Weble\LaravelEcommerce\Discount\DiscountModel::class,
                    'address' => \Weble\LaravelEcommerce\Address\AddressModel::class,*/
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

    'cart'    => [
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
                'prefix'  => 'wishlist_',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Default cart instance
        |--------------------------------------------------------------------------
        |
        | When you add something to the cart, which instance gets selected by default
        */
        'default'   => 'cart',
    ],

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    |
    | Details of the store selling the products.
    */
    'store'   => [
        'address' => [
            'country'      => 'IT',
            'city'         => 'Vicenza',
            'zip'          => '36100',
            'state'        => 'VI',
            'address'      => 'Via Enrico Fermi, 265',
            'address2'     => '',
            'organization' => 'Weble Srl',
            'vat_id'       => '03579410246',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Order
    |--------------------------------------------------------------------------
    |
    | Order Settings, like hash generation and State Machine for managing the order workflow
    */
    'order'   => [

        'hash_length' => 8,

        'clear_cart' => true,

        /*
        |--------------------------------------------------------------------------
        | Order Workflow
        |--------------------------------------------------------------------------
        |
        | Order management workflow, as a state machine
        | See docs for details.
        */
        'workflow'   => [
            'class' => config('ecommerce.classes.orderModel', \Weble\LaravelEcommerce\Order\Order::class),
            'graph' => 'ecommerce-order',
            // Name of the graph passed down to winzou/state-machine

            'property_path' => 'state',
            // should exist on model

            'states'      => [
                \Weble\LaravelEcommerce\Order\OrderState::NEW,
                \Weble\LaravelEcommerce\Order\OrderState::PAYED,
                \Weble\LaravelEcommerce\Order\OrderState::CANCELED,
                \Weble\LaravelEcommerce\Order\OrderState::REFUNDED,
                \Weble\LaravelEcommerce\Order\OrderState::COMPLETED,
            ],
            'transitions' => [
                \Weble\LaravelEcommerce\Order\OrderTransition::PAY => [
                    'from' => [
                        \Weble\LaravelEcommerce\Order\OrderState::NEW,
                    ],
                    'to'   => \Weble\LaravelEcommerce\Order\OrderState::PAYED,
                ],

                \Weble\LaravelEcommerce\Order\OrderTransition::CANCEL => [
                    'from'     => [
                        \Weble\LaravelEcommerce\Order\OrderState::NEW,
                    ],
                    'to'       => \Weble\LaravelEcommerce\Order\OrderState::CANCELED,
                    'metadata' => [
                        'title'   => 'Cancel',
                        'classes' => 'btn btn-default btn-danger',
                    ],
                ],

                \Weble\LaravelEcommerce\Order\OrderTransition::REFUND => [
                    'from' => [
                        \Weble\LaravelEcommerce\Order\OrderState::PAYED,
                        \Weble\LaravelEcommerce\Order\OrderState::COMPLETED,
                    ],
                    'to'   => \Weble\LaravelEcommerce\Order\OrderState::REFUNDED,
                ],


                \Weble\LaravelEcommerce\Order\OrderTransition::COMPLETE => [
                    'from' => [
                        \Weble\LaravelEcommerce\Order\OrderState::PAYED,
                    ],
                    'to'   => \Weble\LaravelEcommerce\Order\OrderState::COMPLETED,
                ],
            ],
            'callbacks'   => [
                'after'  => [
                    'history' => [
                        'do' => new Weble\LaravelEcommerce\Support\StateHistoryManager,
                    ],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    |
    | Payment settings, like the State Machine for managing the payment workflow
    */
    'payment' => [

        /*
        |--------------------------------------------------------------------------
        | Payment Workflow
        |--------------------------------------------------------------------------
        |
        | Payment processing workflow, as a state machine
        | See docs for details.
        */
        'workflow' => [
            'class' => config('ecommerce.classes.paymentModel', \Weble\LaravelEcommerce\Payment\Payment::class),
            'graph' => 'ecommerce-payment',
            // Name of the graph passed down to winzou/state-machine

            'property_path' => 'state',
            // should exist on model

            'states'      => [
                \Weble\LaravelEcommerce\Payment\PaymentState::NEW,
                \Weble\LaravelEcommerce\Payment\PaymentState::PROCESSING,
                \Weble\LaravelEcommerce\Payment\PaymentState::COMPLETED,
                \Weble\LaravelEcommerce\Payment\PaymentState::FAILED,
                \Weble\LaravelEcommerce\Payment\PaymentState::CANCELED,
                \Weble\LaravelEcommerce\Payment\PaymentState::REFUNDED,
            ],
            'transitions' => [
                \Weble\LaravelEcommerce\Payment\PaymentTransition::PROCESS  => [
                    'from' => [
                        \Weble\LaravelEcommerce\Payment\PaymentState::NEW,
                    ],
                    'to'   => \Weble\LaravelEcommerce\Payment\PaymentState::PROCESSING,
                ],
                \Weble\LaravelEcommerce\Payment\PaymentTransition::COMPLETE => [
                    'from' => [
                        \Weble\LaravelEcommerce\Payment\PaymentState::NEW,
                        \Weble\LaravelEcommerce\Payment\PaymentState::PROCESSING,
                    ],
                    'to'   => \Weble\LaravelEcommerce\Payment\PaymentState::COMPLETED,
                ],
                \Weble\LaravelEcommerce\Payment\PaymentTransition::FAIL     => [
                    'from'     => [
                        \Weble\LaravelEcommerce\Payment\PaymentState::NEW,
                        \Weble\LaravelEcommerce\Payment\PaymentState::PROCESSING,
                    ],
                    'to'       => \Weble\LaravelEcommerce\Payment\PaymentState::FAILED,
                    'metadata' => [
                        'title'   => 'Fail',
                        'classes' => 'btn btn-default btn-danger',
                    ],
                ],
                \Weble\LaravelEcommerce\Payment\PaymentTransition::CANCEL   => [
                    'from' => [
                        \Weble\LaravelEcommerce\Payment\PaymentState::NEW,
                        \Weble\LaravelEcommerce\Payment\PaymentState::PROCESSING,
                        \Weble\LaravelEcommerce\Payment\PaymentState::FAILED,
                    ],
                    'to'   => \Weble\LaravelEcommerce\Payment\PaymentState::CANCELED,
                ],
                \Weble\LaravelEcommerce\Payment\PaymentTransition::REFUND   => [
                    'from' => [
                        \Weble\LaravelEcommerce\Payment\PaymentState::COMPLETED,
                    ],
                    'to'   => \Weble\LaravelEcommerce\Payment\PaymentState::REFUNDED,
                ],
            ],
            'callbacks'   => [
                'after' => [
                    'on-completed' => [
                        'to' => \Weble\LaravelEcommerce\Payment\PaymentState::COMPLETED,
                        'do' => new \Weble\LaravelEcommerce\Payment\Callback\MarkOrderAs(\Weble\LaravelEcommerce\Order\OrderTransition::PAY),
                    ],
                    'on-refunded'   => [
                        'to' => \Weble\LaravelEcommerce\Payment\PaymentState::REFUNDED,
                        'do' => new \Weble\LaravelEcommerce\Payment\Callback\MarkOrderAs(\Weble\LaravelEcommerce\Order\OrderTransition::REFUND),
                    ],
                    'history' => [
                        'do' => new Weble\LaravelEcommerce\Support\StateHistoryManager,
                    ],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Taxes
    |--------------------------------------------------------------------------
    |
    | Tax settings
    */

    'tax'     => [
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
    'coupon'  => [
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
        'storageManager'    => \Weble\LaravelEcommerce\Storage\StorageManager::class,
        'currencyManager'   => \Weble\LaravelEcommerce\Currency\CurrencyManager::class,
        'taxManager'        => \Weble\LaravelEcommerce\Tax\TaxManager::class,
        'cartManager'       => \Weble\LaravelEcommerce\Cart\CartManager::class,
        'cartItemModel'     => \Weble\LaravelEcommerce\Cart\CartItemModel::class,
        'cart'              => \Weble\LaravelEcommerce\Cart\Cart::class,
        'orderModel'        => \Weble\LaravelEcommerce\Order\Order::class,
        'orderItemModel'    => \Weble\LaravelEcommerce\Order\OrderItem::class,
        'orderHistoryModel' => \Weble\LaravelEcommerce\Order\StateHistory::class,
        'paymentModel'      => \Weble\LaravelEcommerce\Payment\Payment::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurable Table names
    |--------------------------------------------------------------------------
    |
    | When using the eloquent storage, by default we'll use these table names
    */
    'tables'  => [
        'items'         => 'cart_items',
        'customers'     => 'cart_customers',
        'discounts'     => 'cart_discounts',
        'orders'        => 'orders',
        'order_items'   => 'order_items',
        'state_history' => 'ecommerce_state_history',
        'payments'      => 'payments',
    ],
];
