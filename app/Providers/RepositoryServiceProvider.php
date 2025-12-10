<?php

namespace App\Providers;

use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->singleton(
            ProductRepositoryInterface::class,
            ProductRepository::class,
        );

        $this->app->singleton(
            OrderRepositoryInterface::class,
            OrderRepository::class,
        );

        $this->app->singleton(
            OrderItemRepositoryInterface::class,
            OrderItemRepository::class,
        );
    }
}

