<?php

namespace Weble\LaravelEcommerce\Tests;

use Cknow\Money\MoneyServiceProvider;
use Swap\Laravel\SwapServiceProvider;
use Weble\LaravelEcommerce\LaravelEcommerceServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SwapServiceProvider::class,
            MoneyServiceProvider::class,
            LaravelEcommerceServiceProvider::class,
        ];
    }
}
