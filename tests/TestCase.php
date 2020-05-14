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
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/temp/database.sqlite',
            'prefix' => '',
        ]);
    }

    protected function setupDatabase()
    {
        if (! file_exists(__DIR__ . '/temp/')) {
            @mkdir(__DIR__ . '/temp/');
        }
        $databasePath = __DIR__ . '/temp/database.sqlite';

        if (file_exists($databasePath)) {
            unlink($databasePath);
        }

        if (! file_exists($databasePath)) {
            file_put_contents($databasePath, '');
        }

        $this->app['db']->connection()->getSchemaBuilder()->dropIfExists('cart_items');
        $this->app['db']->connection()->getSchemaBuilder()->create('cart_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('user_id')->nullable();
            $table->uuid('cart_key');
            $table->string('instance')->index();
            $table->bigInteger('purchasable_id');
            $table->string('purchasable_type');
            $table->bigInteger('price');
            $table->float('quantity')->default(1);
            $table->json('product_attributes');
            $table->json('discounts');
            $table->timestamps();

            $table->index(['cart_key']);
            $table->index(['purchasable_type', 'purchasable_id']);
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('price');
        });
    }
}
