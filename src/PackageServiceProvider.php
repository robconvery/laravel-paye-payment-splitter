<?php

namespace Robconvery\LaravelPayePaymentSplitter;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        /*$this->publishes([
            dirname(__DIR__) . '/assets' => resource_path('assets'),
        ], 'payment-splitter');*/
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'PaymentSplitter');
        $this->loadViewsFrom(resource_path('views'), 'app');
        include __DIR__ . '/routes.php';
    }
}
