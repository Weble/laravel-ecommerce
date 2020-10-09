# WARNING: STILL IN DEVELOPMENT - DO NOT USE YET

# Laravel Ecommerce

[![Latest Version on Packagist](https://img.shields.io/packagist/v/weble/laravel-ecommerce.svg?style=flat-square)](https://packagist.org/packages/weble/laravel-ecommerce)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/weble/laravel-ecommerce/run-tests?label=tests)](https://github.com/weble/laravel-ecommerce/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/weble/laravel-ecommerce.svg?style=flat-square)](https://packagist.org/packages/weble/laravel-ecommerce)

Opinionated ecommerce tools for laravel

## Introduction
In a lot of projects, we encounter the same dilemma: we need to provide a customer with an "Ecommerce" website (basically, he needs to sell either a product or a service) which doesn't "fit" in the standard definition of ecommerce (ie: what you can easily build on top of the popular ecommerce CMSes, like Prestashop, Magento, Woocommerce, etc), or with the usual SaaS (Shopify, Webflow, SnipCart, etc)

The natural question that arises for us is then: do we customize these CMSes to suite the particular business case of the client, or build a custom Laravel application while having to deal with all the standard (and always "expected") ecommerce features, like cart, orders, notifications, inventory, coupons, taxes, customers, etc?

This is why Laravel Ecommerce was born: to provide most of these standard ecommerce features to any Laravel Application that needs them, allowing us to leverage them, without having to rebuild them from scratch every time.

It is **very** opinionated, meaning we put some "assertions" in place to allow us to build upon a few "certainties".

### Prerequisites

1. Everything you sell is an Eloquent Model.
1. Everything you sell needs to be added to the cart in order to be purchased.
1. Everything you sell has a price, and the model itself is able to calculate it.

### Design Choices

- In v1 we support only the Single Store model. For v2 we plan to add support for Multiple Store, with a Store Provider.
- Each "thing" you add to the cart is an Eloquent Model. This is done in order to take advantage of the Polymorphic relationships, while keeping the maximum flexibility.
- Everything related to "prices" is a MoneyPHP object. For this we use the excellent ```cknow/laravel-money``` wrapper for laravel
- We use ```commerceguys/addressing``` to deal with addresses and zones in general
- We use ```commerceguys/tax``` to deal with taxes
- We use ```iben12/laravel-statable``` to deal with order management through a state machine.
- We use ```barryvdh/laravel-omnipay``` to deal with payment gateways.
- We provide Laravel Nova Fields / Resources / Tools as an optional way to interact with the package resources.
- We provide views / assets / etc that can be used to speed up the frontend work
- We provide default emails to be sent on specific events
- We trigger a lot of events, to allow for maximum customization
- Every class used and provided can be swapped from the configuration file. 

## Installation

You can install the package via composer:

```bash
composer require weble/laravel-ecommerce
```


# Notes

- Using more than 1 currency => publish swap config and configure it (add cache probably)
- Using payments => publish omnipay config
- Want nova? Install weble/laravel-ecommerce-nova
- TODO: migrations (publish vs load from)
- BEWARE: of changing the default currency if you don't store the currency itself in the db too

## Usage

``` php

```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email daniele@weble.it instead of using the issue tracker.

## Credits

- [Skullbock](https://github.com/skullbock)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
