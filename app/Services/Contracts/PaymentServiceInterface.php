<?php

namespace App\Services\Contracts;

use App\Models\Order;

interface PaymentServiceInterface
{
    /**
     * Get payment token and execute payment
     */
    public function getPaymentToken(Order $order, array $paymentData): array;

    /**
     * Process payment callback from Dinger
     */
    public function callback(array $callbackData): Order;
}

