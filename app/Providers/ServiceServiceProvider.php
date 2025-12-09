<?php

namespace App\Providers;

use App\Services\Contracts\ProductServiceInterface;
use App\Services\ProductService;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register service bindings
     */
    public function register(): void
    {
        // Service bindings
        $this->app->singleton(
            ProductServiceInterface::class,
            ProductService::class
        );

        // Add new service bindings here:
        // $this->app->singleton(
        //     OrderServiceInterface::class,
        //     OrderService::class
        // );
    }
}

