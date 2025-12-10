<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Get all orders with pagination
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders by user ID
     */
    public function getByUserId(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['orderItems.product'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get order by ID
     */
    public function findById(int $id): ?Order
    {
        return Order::with(['user', 'orderItems.product'])->find($id);
    }

    /**
     * Get order by order_id (unique identifier)
     */
    public function findByOrderId(string $orderId): ?Order
    {
        return Order::with(['user', 'orderItems.product'])
            ->where('order_id', $orderId)
            ->first();
    }

    /**
     * Get order by order_id and user_id
     */
    public function findByOrderIdAndUserId(string $orderId, int $userId): ?Order
    {
        return Order::with(['user', 'orderItems.product'])
            ->where('order_id', $orderId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Create a new order
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Update an order
     */
    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    /**
     * Get orders by status
     */
    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['user', 'orderItems.product'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders by payment status
     */
    public function getByPaymentStatus(string $paymentStatus, int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['user', 'orderItems.product'])
            ->where('payment_status', $paymentStatus)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}



