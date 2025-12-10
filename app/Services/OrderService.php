<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\Contracts\OrderServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderServiceInterface
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Get all orders
     */
    public function getAllOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (isset($filters['status'])) {
            return $this->orderRepository->getByStatus($filters['status'], $perPage);
        }

        if (isset($filters['payment_status'])) {
            return $this->orderRepository->getByPaymentStatus($filters['payment_status'], $perPage);
        }

        return $this->orderRepository->getAll($perPage);
    }

    /**
     * Get orders by user ID
     */
    public function getOrdersByUserId(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $orders = $this->orderRepository->getByUserId($userId, $perPage);

        // Apply additional filters if needed
        if (isset($filters['status'])) {
            $orders->getCollection()->transform(function ($order) use ($filters) {
                return $order->status === $filters['status'] ? $order : null;
            })->filter();
        }

        return $orders;
    }

    /**
     * Get order by ID
     *
     * @throws ModelNotFoundException
     */
    public function getOrderById(int $id): Order
    {
        $order = $this->orderRepository->findById($id);

        if (! $order) {
            throw new ModelNotFoundException("Order with ID {$id} not found.");
        }

        return $order;
    }

    /**
     * Get order by order_id
     *
     * @throws ModelNotFoundException
     */
    public function getOrderByOrderId(string $orderId): Order
    {
        $order = $this->orderRepository->findByOrderId($orderId);

        if (! $order) {
            throw new ModelNotFoundException("Order with order_id {$orderId} not found.");
        }

        return $order;
    }

    /**
     * Get order by order_id and user_id
     *
     * @throws ModelNotFoundException
     */
    public function getOrderByOrderIdAndUserId(string $orderId, int $userId): Order
    {
        $order = $this->orderRepository->findByOrderIdAndUserId($orderId, $userId);

        if (! $order) {
            throw new ModelNotFoundException("Order with order_id {$orderId} not found for this user.");
        }

        return $order;
    }

    /**
     * Create a new order
     */
    public function createOrder(int $userId, array $items): Order
    {
        return DB::transaction(function () use ($userId, $items) {
            $totalAmount = 0;
            $orderItems = [];

            // Validate products and calculate total
            foreach ($items as $item) {
                $product = $this->productRepository->findById($item['product_id']);

                if (! $product) {
                    throw new ModelNotFoundException("Product with ID {$item['product_id']} not found.");
                }

                if (! $product->isAvailable()) {
                    throw new \Exception("Product {$product->name} is not available.");
                }

                if (! $product->hasStock($item['quantity'])) {
                    throw new \Exception("Insufficient stock for product {$product->name}.");
                }

                $price = $product->price;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            // Generate unique order ID
            $orderId = 'ORDER-' . time() . '-' . $userId . '-' . uniqid();

            // Create order
            $order = $this->orderRepository->create([
                'user_id' => $userId,
                'order_id' => $orderId,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                $item['order_id'] = $order->id;
                $this->orderItemRepository->create($item);

                // Update product stock
                $product = $this->productRepository->findById($item['product_id']);
                $this->productRepository->update($product, [
                    'stock' => $product->stock - $item['quantity'],
                ]);
            }

            return $order->load('orderItems.product');
        });
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(int $id, string $status): Order
    {
        $order = $this->getOrderById($id);
        $this->orderRepository->update($order, ['status' => $status]);

        return $order->fresh();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(string $orderId, string $paymentStatus, array $paymentData = []): Order
    {
        $order = $this->getOrderByOrderId($orderId);

        $updateData = [
            'payment_status' => $paymentStatus,
        ];

        if ($paymentStatus === 'success') {
            $updateData['payment_completed_at'] = now();
            $updateData['status'] = 'processing';
        } elseif (in_array($paymentStatus, ['failed', 'cancelled'])) {
            $updateData['payment_failed_at'] = now();
            $updateData['status'] = 'failed';
            if (isset($paymentData['failure_reason'])) {
                $updateData['payment_failure_reason'] = $paymentData['failure_reason'];
            }
        }

        if (isset($paymentData['dinger_transaction_id'])) {
            $updateData['dinger_transaction_id'] = $paymentData['dinger_transaction_id'];
        }

        if (isset($paymentData['dinger_provider_name'])) {
            $updateData['dinger_provider_name'] = $paymentData['dinger_provider_name'];
        }

        if (isset($paymentData['dinger_method_name'])) {
            $updateData['dinger_method_name'] = $paymentData['dinger_method_name'];
        }

        $this->orderRepository->update($order, $updateData);

        return $order->fresh();
    }
}



