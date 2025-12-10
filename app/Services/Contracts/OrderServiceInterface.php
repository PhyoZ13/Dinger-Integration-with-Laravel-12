<?php

namespace App\Services\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface OrderServiceInterface
{
    /**
     * Get all orders
     */
    public function getAllOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders by user ID
     */
    public function getOrdersByUserId(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get order by ID
     *
     * @throws ModelNotFoundException
     */
    public function getOrderById(int $id): Order;

    /**
     * Get order by order_id
     *
     * @throws ModelNotFoundException
     */
    public function getOrderByOrderId(string $orderId): Order;

    /**
     * Get order by order_id and user_id
     *
     * @throws ModelNotFoundException
     */
    public function getOrderByOrderIdAndUserId(string $orderId, int $userId): Order;

    /**
     * Create a new order
     */
    public function createOrder(int $userId, array $items): Order;

    /**
     * Update order status
     */
    public function updateOrderStatus(int $id, string $status): Order;

    /**
     * Update payment status
     */
    public function updatePaymentStatus(string $orderId, string $paymentStatus, array $paymentData = []): Order;
}



