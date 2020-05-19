<?php

namespace Weble\LaravelEcommerce\Tests;

use Cknow\Money\MoneyServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Swap\Laravel\SwapServiceProvider;
use Weble\LaravelEcommerce\LaravelEcommerceServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/');
        $this->setupDatabase();
        $this->withFactories(__DIR__.'/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            SwapServiceProvider::class,
            MoneyServiceProvider::class,
            LaravelEcommerceServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        @mkdir(__DIR__ . '/temp/');
        $databasePath = __DIR__ . '/temp/database.sqlite';

        if (file_exists($databasePath)) {
            unlink($databasePath);
        }

        if (! file_exists($databasePath)) {
            file_put_contents($databasePath, '');
        }

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/temp/database.sqlite',
            'prefix'   => '',
        ]);
    }

    protected function setupDatabase()
    {
        $this->loadLaravelMigrations();

        $this->app['db']->connection()->getSchemaBuilder()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('price');
        });
    }
}
