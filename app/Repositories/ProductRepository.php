<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Get all products with pagination
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all active products
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get product by ID
     */
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    /**
     * Create a new product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update a product
     */
    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    /**
     * Delete a product
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Search products by name or description
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

