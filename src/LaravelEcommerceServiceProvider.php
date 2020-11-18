<?php

namespace Weble\LaravelEcommerce;

use Cknow\Money\Money;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepository;
use CommerceGuys\Addressing\AddressFormat\AddressFormatRepositoryInterface;
use CommerceGuys\Addressing\Country\CountryRepository;
use CommerceGuys\Addressing\Country\CountryRepositoryInterface;
use CommerceGuys\Addressing\Formatter\PostalLabelFormatter;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface;
use CommerceGuys\Tax\Repository\TaxTypeRepository;
use CommerceGuys\Tax\Resolver\TaxRate\ChainTaxRateResolver;
use CommerceGuys\Tax\Resolver\TaxRate\ChainTaxRateResolverInterface;
use CommerceGuys\Tax\Resolver\TaxRate\DefaultTaxRateResolver;
use CommerceGuys\Tax\Resolver\TaxResolver;
use CommerceGuys\Tax\Resolver\TaxResolverInterface;
use CommerceGuys\Tax\Resolver\TaxType\CanadaTaxTypeResolver;
use CommerceGuys\Tax\Resolver\TaxType\ChainTaxTypeResolver;
use CommerceGuys\Tax\Resolver\TaxType\ChainTaxTypeResolverInterface;
use CommerceGuys\Tax\Resolver\TaxType\DefaultTaxTypeResolver;
use CommerceGuys\Tax\Resolver\TaxType\EuTaxTypeResolver;
use Illuminate\Support\ServiceProvider;
use Weble\LaravelEcommerce\Cart\CartManager;
use Weble\LaravelEcommerce\Currency\CurrencyManager;
use Weble\LaravelEcommerce\Facades\Cart;
use Weble\LaravelEcommerce\Facades\Currency;
use Weble\LaravelEcommerce\Storage\StorageManager;
use Weble\LaravelEcommerce\Tax\TaxManager;

class LaravelEcommerceServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishResources();
        $this->addMoneyConfig();
        $this->addStateMachineConfig();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ecommerce.php', 'ecommerce');

        $this->addStateMachineConfig();
        $this->registerStorageManager();
        $this->registerCurrencyManager();
        $this->registerTaxClasses();
        $this->registerAddressClasses();
        $this->registerCartManager();

        $this->registerFacades();
    }

    protected function registerCurrencyManager()
    {
        $this->app->singleton('ecommerce.currency', function ($app) {
            $class = $this->app['config']['ecommerce.classes.currencyManager'] ?? CurrencyManager::class;

            return new $class($app);
        });
    }

    protected function registerCartManager()
    {
        $this->app->singleton('ecommerce.cart', function ($app) {
            $class = $this->app['config']['ecommerce.classes.cartManager'] ?? CartManager::class;

            return new $class($app);
        });
    }

    protected function registerTaxClasses()
    {
        $this->app->singleton('ecommerce.tax.resolver', TaxResolver::class);

        $this->app->singleton('ecommerce.tax.chainTaxRateResolver', function ($app) {
            $chainTaxRateResolver = new ChainTaxRateResolver();
            $chainTaxRateResolver->addResolver(new DefaultTaxRateResolver());

            return $chainTaxRateResolver;
        });

        $this->app->singleton('ecommerce.tax.chainTaxTypeResolver', function ($app) {
            $taxTypeRepository = new TaxTypeRepository();
            $chainTaxTypeResolver = new ChainTaxTypeResolver();
            $chainTaxTypeResolver->addResolver(new CanadaTaxTypeResolver($taxTypeRepository));
            $chainTaxTypeResolver->addResolver(new EuTaxTypeResolver($taxTypeRepository));
            $chainTaxTypeResolver->addResolver(new DefaultTaxTypeResolver($taxTypeRepository));

            return $chainTaxTypeResolver;
        });

        $this->app->bind(TaxResolverInterface::class, TaxResolver::class);
        $this->app->bind(ChainTaxRateResolverInterface::class, ChainTaxRateResolver::class);
        $this->app->bind(ChainTaxTypeResolverInterface::class, ChainTaxTypeResolver::class);

        $this->app->when(TaxResolver::class)
            ->needs('$chainTaxTypeResolver')
            ->give(app('ecommerce.tax.chainTaxTypeResolver'));

        $this->app->when(TaxResolver::class)
            ->needs('$chainTaxRateResolver')
            ->give(app('ecommerce.tax.chainTaxRateResolver'));

        $this->app->singleton('ecommerce.tax', function ($app) {
            $class = $this->app['config']['ecommerce.classes.taxManager'] ?? TaxManager::class;

            return new $class($app);
        });
    }

    protected function registerAddressClasses()
    {
        $this->app->bind(AddressFormatRepositoryInterface::class, AddressFormatRepository::class);
        $this->app->bind(CountryRepositoryInterface::class, CountryRepository::class);
        $this->app->bind(SubdivisionRepositoryInterface::class, SubdivisionRepository::class);

        $this->app->singleton('ecommerce.addressFormatRepository', AddressFormatRepository::class);
        $this->app->singleton('ecommerce.countryRepository', CountryRepository::class);
        $this->app->singleton('ecommerce.subdivisionRepository', SubdivisionRepository::class);

        $this->app->when(PostalLabelFormatter::class)
            ->needs('$defaultOptions')
            ->give([
                'origin_country' => config('ecommerce.store.address.country', 'IT'),
            ]);
    }

    protected function registerStorageManager()
    {
        $this->app->singleton('ecommerce.storage', function ($app) {
            $class = $this->app['config']['ecommerce.classes.storageManager'] ?? StorageManager::class;

            return new $class($app);
        });
    }

    protected function registerFacades()
    {
        $this->app->alias('ecommerce.storage', Cart::class);
        $this->app->alias('ecommerce.cart', Cart::class);
        $this->app->alias('ecommerce.currency', Currency::class);
        $this->app->alias('ecommerce.tax', TaxManager::class);
    }

    public function provides()
    {
        return [
            'ecommerce.storage',
            'ecommerce.cart',
            'ecommerce.currency',
            'ecommerce.tax',
        ];
    }

    protected function publishResources(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/ecommerce.php' => config_path('ecommerce.php'),
        ], 'config');

        if (! class_exists('CreateCartitemsTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/0000_00_00_000000_create_cartitems_table.php' => database_path('migrations/' . $timestamp . '_create_cartitems_table.php'),
            ], 'migrations');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/');
        }

        /*
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'skeleton');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/skeleton'),
        ], 'views');
        */
    }

    protected function addStateMachineConfig(): void
    {
        $stateMachineConfigKeys = [
            'order',
            'payment',
        ];

        $keys = [];
        foreach ($stateMachineConfigKeys as $key) {
            $graphKey  = 'ecommerce.' . $key . '.workflow';
            $configKey = config($graphKey . '.graph', 'ecommerce-' . $key);
            config()->set('state-machine.' . $configKey, config()->get($graphKey));
            $keys[$graphKey] = config($graphKey . '.graph', 'ecommerce-' . $key);
        }

        $this->app->extend('sm.factory', function ($service, $app) use ($keys) {
            foreach ($keys as $graphKey => $key) {
                $service->addConfig(config()->get($graphKey), $key);
            }

            return $service;
        });

        $this->app->resolving('sm.factory', function ($service, $app) use ($keys) {
            foreach ($keys as $graphKey => $key) {
                $service->addConfig(config()->get($graphKey), $key);
            }
        });
    }

    protected function addMoneyConfig(): void
    {
        Money::setLocale(config()->get('ecommerce.customer.locale', 'en_US'));
        Money::setDefaultCurrency(config()->get('ecommerce.currency.default', 'USD'));
    }
}
