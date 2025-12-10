<?php

namespace App\Services;

use App\Models\Order;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentService implements PaymentServiceInterface
{
    // Dinger API Endpoints
    private const AUTH_URL = "https://encryption.dinger.asia/api/auth";
    private const ENCRYPT_URL = "https://encryption.dinger.asia/api/rsa-encrypt";
    private const TOKEN_URL = "https://api.dinger.asia/api/token";
    private const PAY_URL = "https://api.dinger.asia/api/pay";

    private string $encryptionEmail;
    private string $encryptionPassword;
    private string $dingerApiKey;
    private string $dingerPublicKey;
    private string $projectName;
    private string $merchantName;
    private string $callbackKey;

    public function __construct(
        protected OrderServiceInterface $orderService
    ) {
        $this->encryptionEmail = env('DINGER_ENCRYPTION_EMAIL');
        $this->encryptionPassword = env('DINGER_ENCRYPTION_PASSWORD');
        $this->dingerApiKey = env('DINGER_API_KEY');
        $this->dingerPublicKey = env('DINGER_PUBLIC_KEY');
        $this->projectName = env('DINGER_PROJECT_NAME');
        $this->merchantName = env('DINGER_MERCHANT_NAME');
        $this->callbackKey = env('DINGER_CALLBACK_KEY');
    }

    /**
     * Get payment token and execute payment
     */
    public function getPaymentToken(Order $order, array $paymentData): array
    {
        try {
            // Step 1: Get encryption authentication token
            $authToken = $this->getEncryptionToken();

            // Step 2: Prepare payment payload
            $payloadData = $this->preparePayload($order, $paymentData);

            // Step 3: Encrypt the payload
            $encryptedPayload = $this->encryptPayload($authToken, $payloadData);

            // Step 4: Get payment token
            $paymentToken = $this->fetchPaymentToken();

            // Step 5: Execute payment
            $response = $this->executePayment($paymentToken, $encryptedPayload);

            // Step 6: Update order with transaction details
            if (isset($response['response']['transactionNum'])) {
                $this->orderService->updatePaymentStatus($order->order_id, 'pending', [
                    'dinger_transaction_id' => $response['response']['transactionNum'],
                    'dinger_provider_name' => $paymentData['providerName'] ?? null,
                    'dinger_method_name' => $paymentData['methodName'] ?? null,
                ]);
            }

            return $response;
        } catch (Exception $e) {
            Log::channel('dinger_payment')->error('Payment initiation failed', [
                'order_id' => $order->order_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process payment callback from Dinger
     */
    public function callback(array $callbackData): Order
    {
        try {
            // Decrypt and verify callback data
            $decryptedValues = $this->decryptCallback($callbackData);

            // Extract transaction information
            $transactionStatus = $decryptedValues["transactionStatus"] ?? null;
            $merchantOrderId = $decryptedValues["merchantOrderId"] ?? null;
            $transactionId = $decryptedValues["transactionId"] ?? null;

            if (! $merchantOrderId || ! $transactionId) {
                throw new Exception('Missing required fields in callback data.');
            }

            // Map Dinger status to our payment status
            $paymentStatus = $this->mapTransactionStatus($transactionStatus);

            // Update order payment status
            $order = $this->orderService->updatePaymentStatus($merchantOrderId, $paymentStatus, [
                'dinger_transaction_id' => $transactionId,
                'failure_reason' => $transactionStatus !== 'SUCCESS' ? "Payment status: {$transactionStatus}" : null,
            ]);

            return $order;
        } catch (Exception $e) {
            Log::channel('dinger_payment')->error('Callback processing failed', [
                'error' => $e->getMessage(),
                'data' => $callbackData,
            ]);
            throw $e;
        }
    }

    /**
     * Get encryption authentication token from Dinger
     */
    private function getEncryptionToken(): string
    {
        $response = Http::post(self::AUTH_URL, [
            "email" => $this->encryptionEmail,
            "password" => $this->encryptionPassword,
        ]);

        if ($response->failed() || ! ($response->json()['token'] ?? null)) {
            Log::error("Failed to fetch encryption token", $response->json());
            throw new Exception("Failed to fetch encryption token from Dinger auth service.");
        }

        return $response->json()['token'];
    }

    /**
     * Prepare payment payload data
     */
    private function preparePayload(Order $order, array $paymentData): array
    {
        // Build items array from order items
        $items = [];
        foreach ($order->orderItems as $item) {
            $items[] = [
                "name" => $item->product->name,
                "amount" => (float) $item->price,
                "quantity" => $item->quantity,
            ];
        }

        // Base payload data
        $payloadData = [
            "providerName" => $paymentData['providerName'],
            "methodName" => strtoupper(trim($paymentData['methodName'])),
            "totalAmount" => (float) $order->total_amount,
            "items" => json_encode($items),
            "orderId" => $order->order_id,
            "customerName" => $paymentData['customerName'] ?? $order->user->name ?? '',
            "customerPhone" => $this->normalizePhone($paymentData['customerPhone'] ?? $order->user->phone ?? ''),
            "description" => $this->projectName,
        ];

        // Add credit card specific fields if needed
        $isCreditCard = in_array($paymentData['providerName'], ['Visa', 'Master', 'JCB']);
        if ($isCreditCard) {
            if (isset($paymentData['email'])) {
                $payloadData['email'] = $paymentData['email'];
            }
            if (isset($paymentData['billAddress'])) {
                $payloadData['billAddress'] = $paymentData['billAddress'];
            }
            if (isset($paymentData['billCity'])) {
                $payloadData['billCity'] = $paymentData['billCity'];
            }
        }

        return $payloadData;
    }

    /**
     * Encrypt payload using Dinger encryption service
     */
    private function encryptPayload(string $authToken, array $payloadData): string
    {
        $response = Http::withToken($authToken)->post(
            self::ENCRYPT_URL,
            [
                "data" => json_encode($payloadData),
                "publicKey" => $this->dingerPublicKey,
            ]
        );

        if ($response->failed() || ! ($response->json()['encrypted_data'] ?? null)) {
            Log::error("Payload encryption failed", $response->json());
            throw new Exception("Payload encryption failed via Dinger service.");
        }

        return $response->json()['encrypted_data'];
    }

    /**
     * Fetch payment token from Dinger API
     */
    private function fetchPaymentToken(): string
    {
        $response = Http::get(self::TOKEN_URL, [
            "projectName" => $this->projectName,
            "apiKey" => $this->dingerApiKey,
            "merchantName" => $this->merchantName,
        ]);

        $paymentTokenData = $response->json();
        $paymentToken = $paymentTokenData['response']['paymentToken'] ?? null;

        if (! $paymentToken) {
            Log::error("Payment token failed", $paymentTokenData);
            throw new Exception("Failed to fetch payment token from Dinger API.");
        }

        return $paymentToken;
    }

    /**
     * Execute payment via Dinger Pay API
     */
    private function executePayment(string $paymentToken, string $encryptedPayload): array
    {
        $response = Http::withToken($paymentToken)
            ->asForm()
            ->post(self::PAY_URL, [
                "payload" => $encryptedPayload,
            ]);

        if ($response->failed()) {
            Log::error("Pay API call failed", $response->json());
            throw new Exception("Dinger Pay API failed with status: " . $response->status());
        }

        $responseData = $response->json();
        Log::channel('dinger_payment')->info('Payment response', $responseData);

        if ($responseData['code'] !== '000') {
            throw new Exception('Payment failed: ' . ($responseData['message'] ?? 'Unknown error'));
        }

        return $responseData;
    }

    /**
     * Decrypt and verify Dinger callback data
     */
    private function decryptCallback(array $callbackData): array
    {
        $paymentResult = $callbackData['paymentResult'] ?? null;
        $checkSum = $callbackData['checksum'] ?? null;

        if (! $paymentResult || ! $checkSum) {
            throw new Exception('Missing paymentResult or checksum in callback data.');
        }

        // Decrypt payment result
        $decrypted = openssl_decrypt($paymentResult, "AES-256-ECB", $this->callbackKey);

        if ($decrypted === false) {
            throw new Exception('Failed to decrypt payment result.');
        }

        // Verify checksum
        if (hash("sha256", $decrypted) !== $checkSum) {
            throw new Exception('Checksum verification failed.');
        }

        $decryptedValues = json_decode($decrypted, true);

        if (! $decryptedValues) {
            throw new Exception('Failed to decode decrypted data.');
        }

        Log::channel('dinger_payment')->info('Callback decrypted', $decryptedValues);

        return $decryptedValues;
    }

    /**
     * Normalize phone number to Myanmar format (09XXXXXXXXX)
     */
    private function normalizePhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Handle +959 format
        if (str_starts_with($phone, '+959')) {
            $phone = '09' . substr($phone, 4);
        }
        // Handle 959 format
        elseif (str_starts_with($phone, '959')) {
            $phone = '09' . substr($phone, 3);
        }
        // If it doesn't start with 09, try to add it if it's 9 digits
        elseif (strlen($phone) === 9 && ! str_starts_with($phone, '09')) {
            $phone = '09' . $phone;
        }

        return $phone;
    }

    /**
     * Map Dinger transaction status to our payment status
     */
    private function mapTransactionStatus(?string $transactionStatus): string
    {
        return match ($transactionStatus) {
            'SUCCESS' => 'success',
            'ERROR', 'CANCELLED', 'TIMEOUT', 'DECLINED', 'SYSTEM_ERROR' => 'failed',
            default => 'pending',
        };
    }
}
