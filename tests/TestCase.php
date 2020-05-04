<?php


namespace Weble\LaravelEcommerce\Tests;


use Weble\LaravelEcommerce\LaravelEcommerceServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelEcommerceServiceProvider::class,
        ];
    }
}
