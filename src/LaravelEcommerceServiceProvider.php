<?php

namespace Weble\LaravelEcommerce;

use Cknow\Money\Money;
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
use Weble\LaravelEcommerce\Tax\TaxManager;

class LaravelEcommerceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishResources();

        Money::setLocale($this->app->make('config')->get('ecommerce.locale'));
        Money::setCurrency($this->app->make('config')->get('ecommerce.currency.default'));
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'ecommerce');

        $this->registerCurrencyManager();
        $this->registerTaxClasses();
        $this->registerCartInstances();
        $this->registerCartManager();

        $this->registerFacades();
    }

    protected function registerCurrencyManager()
    {
        $this->app->singleton('ecommerce.currencyManager', function ($app) {
            $class = $this->app['config']['ecommerce.classes.currencyManager'] ?? CurrencyManager::class;

            return new $class($app);
        });
    }

    protected function registerCartManager()
    {
        $this->app->singleton('ecommerce.cartManager', function ($app) {
            $class = $this->app['config']['ecommerce.classes.cartManager'] ?? CartManager::class;

            return new $class($app);
        });
    }

    protected function registerCartInstances()
    {
        $instances = array_keys($this->app['config']['ecommerce.cart.instances'] ?? []);
        foreach ($instances as $instance) {
            $this->app->singleton('ecommerce.cart.instance.' . $instance, function ($app) use ($instance) {
                return $app['ecommerce.cartManager']->instance($instance);
            });
        }
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

        $this->app->singleton('ecommerce.taxManager', function ($app) {
            $class = $this->app['config']['ecommerce.classes.taxManager'] ?? TaxManager::class;
            return new $class($app);
        });
    }

    protected function registerFacades()
    {
        $this->app->alias('ecommerce.cartManager', Cart::class);
        $this->app->alias('ecommerce.currencyManager', Currency::class);
        $this->app->alias('ecommerce.taxManager', TaxManager::class);
    }

    protected function publishResources(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('ecommerce.php'),
        ], 'config');

        if (! class_exists('CreateCartitemsTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/0000_00_00_000000_create_cartitems_table.php' => database_path('migrations/'.$timestamp.'_create_cartitems_table.php'),
            ], 'migrations');
        }

        /*
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'skeleton');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/skeleton'),
        ], 'views');
        */
    }
}
