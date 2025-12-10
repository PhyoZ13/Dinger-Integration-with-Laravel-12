<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    /**
     * Get all orders with pagination
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders by user ID
     */
    public function getByUserId(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get order by ID
     */
    public function findById(int $id): ?Order;

    /**
     * Get order by order_id (unique identifier)
     */
    public function findByOrderId(string $orderId): ?Order;

    /**
     * Get order by order_id and user_id
     */
    public function findByOrderIdAndUserId(string $orderId, int $userId): ?Order;

    /**
     * Create a new order
     */
    public function create(array $data): Order;

    /**
     * Update an order
     */
    public function update(Order $order, array $data): bool;

    /**
     * Get orders by status
     */
    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders by payment status
     */
    public function getByPaymentStatus(string $paymentStatus, int $perPage = 15): LengthAwarePaginator;
}



