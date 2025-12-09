<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Get all products
     */
    public function getAllProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (isset($filters['status']) && $filters['status'] === 'active') {
            return $this->productRepository->getActive($perPage);
        }

        if (isset($filters['search'])) {
            return $this->productRepository->search($filters['search'], $perPage);
        }

        return $this->productRepository->getAll($perPage);
    }

    /**
     * Get product by ID
     *
     * @throws ModelNotFoundException
     */
    public function getProductById(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if (! $product) {
            throw new ModelNotFoundException("Product with ID {$id} not found.");
        }

        return $product;
    }

    /**
     * Create a new product
     */
    public function createProduct(array $data): Product
    {
        // Set default status if not provided
        if (! isset($data['status'])) {
            $data['status'] = 'active';
        }

        return $this->productRepository->create($data);
    }

    /**
     * Update a product
     *
     * @throws ModelNotFoundException
     */
    public function updateProduct(int $id, array $data): Product
    {
        $product = $this->getProductById($id);
        $this->productRepository->update($product, $data);

        return $product->fresh();
    }

    /**
     * Delete a product
     *
     * @throws ModelNotFoundException
     */
    public function deleteProduct(int $id): bool
    {
        $product = $this->getProductById($id);

        return $this->productRepository->delete($product);
    }
}

