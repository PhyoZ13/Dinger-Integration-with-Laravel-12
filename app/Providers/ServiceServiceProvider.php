<?php

namespace App\Providers;

use App\Services\Contracts\OrderServiceInterface;
use App\Services\Contracts\PaymentServiceInterface;
use App\Services\Contracts\ProductServiceInterface;
use App\Services\OrderService;
use App\Services\PaymentService;
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
            ProductService::class,
        );
        
        $this->app->singleton(
            OrderServiceInterface::class,
            OrderService::class
        );

        $this->app->singleton(
            PaymentServiceInterface::class,
            PaymentService::class,
        );
    }
}

