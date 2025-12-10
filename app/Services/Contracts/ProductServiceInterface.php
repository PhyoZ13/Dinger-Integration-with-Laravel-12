<?php

namespace App\Services\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    /**
     * Get all products
     */
    public function getAllProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get product by ID
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getProductById(int $id): Product;

    /**
     * Create a new product
     */
    public function createProduct(array $data): Product;

    /**
     * Update a product
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateProduct(int $id, array $data): Product;

    /**
     * Delete a product
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteProduct(int $id): bool;
}




