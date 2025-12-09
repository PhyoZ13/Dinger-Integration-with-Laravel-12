<?php

namespace App\Providers;

use App\Repositories\Contracts\ProductRepositoryInterface;
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
            ProductRepository::class
        );

        // Add new repository bindings here:
        // $this->app->singleton(
        //     OrderRepositoryInterface::class,
        //     OrderRepository::class
        // );
    }
}

