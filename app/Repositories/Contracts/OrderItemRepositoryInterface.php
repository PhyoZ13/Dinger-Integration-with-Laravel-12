<?php

namespace App\Repositories\Contracts;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;

interface OrderItemRepositoryInterface
{
    /**
     * Get all order items for an order
     */
    public function getByOrderId(int $orderId): Collection;

    /**
     * Create an order item
     */
    public function create(array $data): OrderItem;

    /**
     * Create multiple order items
     */
    public function createMany(array $items): bool;

    /**
     * Update an order item
     */
    public function update(OrderItem $orderItem, array $data): bool;

    /**
     * Delete an order item
     */
    public function delete(OrderItem $orderItem): bool;
}



