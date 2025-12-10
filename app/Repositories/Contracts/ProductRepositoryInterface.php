<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * Get all products with pagination
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all active products
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get product by ID
     */
    public function findById(int $id): ?Product;

    /**
     * Create a new product
     */
    public function create(array $data): Product;

    /**
     * Update a product
     */
    public function update(Product $product, array $data): bool;

    /**
     * Delete a product
     */
    public function delete(Product $product): bool;

    /**
     * Search products by name or description
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator;
}




