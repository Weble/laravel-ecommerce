<?php

namespace Weble\LaravelEcommerce\Tests;

use Cknow\Money\MoneyServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Swap\Laravel\SwapServiceProvider;
use Weble\LaravelEcommerce\LaravelEcommerceServiceProvider;
use Weble\LaravelEcommerce\Tests\mocks\User;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/');
        $this->setupDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            SwapServiceProvider::class,
            MoneyServiceProvider::class,
            \Sebdesign\SM\ServiceProvider::class,
            \Iben\Statable\ServiceProvider::class,
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

        $app['config']->set('ecommerce.classes.user', User::class);
    }

    protected function setupDatabase()
    {
        $this->loadLaravelMigrations();

        $this->app['db']->connection()->getSchemaBuilder()->create('state_history', function (Blueprint $table) {
            $table->increments('id');
            $table->string('transition');
            $table->string('from');
            $table->string('to');
            $table->integer('actor_id')->nullable();
            $table->morphs('statable');
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('price');
        });
    }
}
