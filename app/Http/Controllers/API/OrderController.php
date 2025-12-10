<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\Contracts\OrderServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderServiceInterface $orderService
    ) {
    }

    /**
     * Display a listing of the orders
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->input('status'),
            'payment_status' => $request->input('payment_status'),
        ];

        $perPage = $request->input('per_page', 15);

        // Get orders for authenticated user
        $orders = $this->orderService->getOrdersByUserId(
            $request->user()->id,
            $filters,
            $perPage
        );

        return $this->paginatedResponse(
            OrderResource::collection($orders),
            'Orders retrieved successfully'
        );
    }

    /**
     * Store a newly created order
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder(
                $request->user()->id,
                $request->validated()['items']
            );

            return $this->createdResponse(
                ['order' => new OrderResource($order)],
                'Order created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    /**
     * Display the specified order
     */
    public function show(string $orderId, Request $request): JsonResponse
    {
        try {
            $order = $this->orderService->getOrderByOrderIdAndUserId(
                $orderId,
                $request->user()->id
            );

            return $this->successResponse(
                ['order' => new OrderResource($order)],
                'Order retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Order not found');
        }
    }
}



