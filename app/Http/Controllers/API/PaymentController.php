<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PaymentTokenRequest;
use App\Http\Resources\OrderResource;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected OrderServiceInterface $orderService,
        protected PaymentServiceInterface $paymentService
    ) {
    }

    /**
     * Get payment token and execute payment
     */
    public function getPaymentToken(PaymentTokenRequest $request): JsonResponse
    {
        try {
            // Get the order
            $order = $this->orderService->getOrderByOrderIdAndUserId(
                $request->validated()['order_id'],
                $request->user()->id
            );

            // Check if order is already paid
            if ($order->isPaid()) {
                return $this->errorResponse('Order is already paid', null, 400);
            }

            // Prepare payment data
            $paymentData = [
                'providerName' => $request->providerName,
                'methodName' => $request->methodName,
                'customerName' => $request->customerName,
                'customerPhone' => $request->customerPhone,
                'email' => $request->email,
                'billAddress' => $request->billAddress,
                'billCity' => $request->billCity,
            ];

            // Get payment token and execute payment
            $response = $this->paymentService->getPaymentToken($order, $paymentData);

            return $this->successResponse(
                [
                    'order' => new OrderResource($order->fresh()),
                    'payment_response' => $response,
                ],
                'Payment initiated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Order not found');
        } catch (\Exception $e) {
            Log::channel('dinger_payment')->error('Payment token error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                'Payment initiation failed: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Get order detail
     */
    public function getOrderDetail(string $orderId, Request $request): JsonResponse
    {
        try {
            $order = $this->orderService->getOrderByOrderIdAndUserId(
                $orderId,
                $request->user()->id
            );

            return $this->successResponse(
                ['order' => new OrderResource($order)],
                'Order detail retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Order not found');
        }
    }

    /**
     * Handle Dinger payment callback
     */
    public function dingerCallback(Request $request): JsonResponse
    {
        try {
            Log::channel('dinger_payment')->info('Dinger callback received', $request->all());

            $order = $this->paymentService->callback($request->all());

            return $this->successResponse(
                ['order' => new OrderResource($order)],
                'Callback processed successfully'
            );
        } catch (\Exception $e) {
            Log::channel('dinger_payment')->error('Dinger callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);

            return $this->errorResponse(
                'Callback processing failed: ' . $e->getMessage(),
                null,
                500
            );
        }
    }
}

