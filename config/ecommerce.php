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
            ],

            'eloquent' => [
                'fallback'    => 'session',
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
            'default_state' => \Weble\LaravelEcommerce\Order\OrderState::New->value(),
            'class' => config('ecommerce.classes.orderModel', \Weble\LaravelEcommerce\Order\Order::class),
            'graph' => 'ecommerce-order',
            // Name of the graph passed down to winzou/state-machine

            'property_path' => 'state',
            // should exist on model

            'states'      => [
                \Weble\LaravelEcommerce\Order\OrderState::New->value(),
                \Weble\LaravelEcommerce\Order\OrderState::Payed->value(),
                \Weble\LaravelEcommerce\Order\OrderState::Canceled->value(),
                \Weble\LaravelEcommerce\Order\OrderState::Refunded->value(),
                \Weble\LaravelEcommerce\Order\OrderState::Completed->value(),
            ],
            'transitions' => [
                \Weble\LaravelEcommerce\Order\OrderTransition::Pay->value => [
                    'from' => [
                        \Weble\LaravelEcommerce\Order\OrderState::New->value(),
                    ],
                    'to'   => \Weble\LaravelEcommerce\Order\OrderState::Payed->value(),
                ],

                \Weble\LaravelEcommerce\Order\OrderTransition::Cancel->value => [
                    'from'     => [
                        \Weble\LaravelEcommerce\Order\OrderState::New->value(),
                    ],
                    'to'       => \Weble\LaravelEcommerce\Order\OrderState::Canceled->value(),
                    'metadata' => [
                        'title'   => 'Cancel',
                        'classes' => 'btn btn-default btn-danger',
                    ],
                ],

                \Weble\LaravelEcommerce\Order\OrderTransition::Refund->value => [
                    'from' => [
                        \Weble\LaravelEcommerce\Order\OrderState::Payed->value(),
                        \Weble\LaravelEcommerce\Order\OrderState::Completed->value(),
                    ],
                    'to'   => \Weble\LaravelEcommerce\Order\OrderState::Refunded->value(),
                ],

                \Weble\LaravelEcommerce\Order\OrderTransition::Complete->value => [
                    'from' => [
                        \Weble\LaravelEcommerce\Order\OrderState::Payed->value(),
                    ],
                    'to'   => \Weble\LaravelEcommerce\Order\OrderState::Completed->value(),
                ],
            ],
            'callbacks'   => [
                'after' => [
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
                \Weble\LaravelEcommerce\Payment\PaymentState::Created->value(),
                \Weble\LaravelEcommerce\Payment\PaymentState::Processing->value(),
                \Weble\LaravelEcommerce\Payment\PaymentState::Completed->value(),
                \Weble\LaravelEcommerce\Payment\PaymentState::Failed->value(),
                \Weble\LaravelEcommerce\Payment\PaymentState::Canceled->value(),
                \Weble\LaravelEcommerce\Payment\PaymentState::Refunded->value(),
            ],
            'transitions' => [
                \Weble\LaravelEcommerce\Payment\PaymentTransition::Process->value  => [
                    'from' => [
                        \Weble\LaravelEcommerce\Payment\PaymentState::Created->value(),
                    ],
                    'to'   => \Weble\LaravelEcommerce\Payment\PaymentState::Processing->value(),
                ],
                \Weble\LaravelEcommerce\Payment\PaymentTransition::Complete->value => [
                    'from' => [
                        \Weble\LaravelEcommerce\Payment\PaymentState::Created->value(),
                        \Weble\LaravelEcommerce\Payment\PaymentState::Processing->value(),
                    ],
                    'to'   => \Weble\LaravelEcommerce\Payment\PaymentState::Completed->value(),
                ],
                \Weble\LaravelEcommerce\Payment\PaymentTransition::Fail->value     => [
                    'from'     => [
                        \Weble\LaravelEcommerce\Payment\PaymentState::Created->value(),
                        \Weble\LaravelEcommerce\Payment\PaymentState::Processing->value(),
                    ],
                    'to'       => \Weble\LaravelEcommerce\Payment\PaymentState::Failed->value(),
                    'metadata' => [
                        'title'   => 'Fail',
                        'classes' => 'btn btn-default btn-danger',
                    ],
                ],
                \Weble\LaravelEcommerce\Payment\PaymentTransition::Cancel->value   => [
                    'from' => [
                        \Weble\LaravelEcommerce\Payment\PaymentState::Created->value(),
                        \Weble\LaravelEcommerce\Payment\PaymentState::Processing->value(),
                        \Weble\LaravelEcommerce\Payment\PaymentState::Failed->value(),
                    ],
                    'to'   => \Weble\LaravelEcommerce\Payment\PaymentState::Canceled->value(),
                ],
                \Weble\LaravelEcommerce\Payment\PaymentTransition::Refund->value   => [
                    'from' => [
                        \Weble\LaravelEcommerce\Payment\PaymentState::Completed->value(),
                    ],
                    'to'   => \Weble\LaravelEcommerce\Payment\PaymentState::Refunded->value(),
                ],
            ],
            'callbacks'   => [
                'after' => [
                    'on-completed' => [
                        'to' => \Weble\LaravelEcommerce\Payment\PaymentState::Completed->value(),
                        'do' => new \Weble\LaravelEcommerce\Payment\Callback\MarkOrderAs(\Weble\LaravelEcommerce\Order\OrderTransition::Pay),
                    ],
                    'on-refunded'  => [
                        'to' => \Weble\LaravelEcommerce\Payment\PaymentState::Refunded->value(),
                        'do' => new \Weble\LaravelEcommerce\Payment\Callback\MarkOrderAs(\Weble\LaravelEcommerce\Order\OrderTransition::Refund),
                    ],
                    'history'      => [
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
        'address_type' => \Weble\LaravelEcommerce\Address\AddressType::Shipping,

       /*
       |--------------------------------------------------------------------------
       | Check Valid VAT ID
       |--------------------------------------------------------------------------
       |
       | In case of EU VAT, should the VAT ID be checked realtime to consider the company valid?
       | Defaults to true.
       */
        'vat_id_check' => true,
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
        'user'              => \App\Models\User::class,
        'stateMachine'      => \Weble\LaravelEcommerce\StateMachine::class,
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
        'customerModel'     => \Weble\LaravelEcommerce\Customer\CustomerModel::class,
        'addressModel'      => \Weble\LaravelEcommerce\Address\AddressModel::class,
        'discountModel'     => \Weble\LaravelEcommerce\Discount\DiscountModel::class,
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
