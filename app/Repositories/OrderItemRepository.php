<?php

namespace App\Repositories;

use App\Models\OrderItem;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    /**
     * Get all order items for an order
     */
    public function getByOrderId(int $orderId): Collection
    {
        return OrderItem::with('product')
            ->where('order_id', $orderId)
            ->get();
    }

    /**
     * Create an order item
     */
    public function create(array $data): OrderItem
    {
        return OrderItem::create($data);
    }

    /**
     * Create multiple order items
     */
    public function createMany(array $items): bool
    {
        return OrderItem::insert($items);
    }

    /**
     * Update an order item
     */
    public function update(OrderItem $orderItem, array $data): bool
    {
        return $orderItem->update($data);
    }

    /**
     * Delete an order item
     */
    public function delete(OrderItem $orderItem): bool
    {
        return $orderItem->delete();
    }
}



