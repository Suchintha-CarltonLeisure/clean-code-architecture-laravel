<?php

namespace App\Infrastructure\Services;

use App\Application\DTOs\MoneyDTO;
use App\Domain\Models\Order\Order;
use Illuminate\Support\Facades\Log;

final class PaymentService
{
    private string $apiKey;
    private string $endpoint;
    private bool $isTestMode;

    public function __construct(string $apiKey, string $endpoint, bool $isTestMode = false)
    {
        $this->apiKey = $apiKey;
        $this->endpoint = $endpoint;
        $this->isTestMode = $isTestMode;
    }

    /**
     * Process a payment for an order
     */
    public function processPayment(Order $order, MoneyDTO $amount, array $paymentMethod): array
    {
        try {
            // Validate payment method
            if (!$this->validatePaymentMethod($paymentMethod['type'])) {
                throw new \InvalidArgumentException('Invalid payment method');
            }

            // Prepare payment data
            $paymentData = [
                'order_id' => $order->getId(),
                'amount' => $amount->getAmount(),
                'currency' => $amount->getCurrency(),
                'customer_name' => $order->getCustomerName(),
                'payment_method' => $paymentMethod,
                'timestamp' => time(),
                'test_mode' => $this->isTestMode
            ];

            // Log payment attempt
            Log::info('Processing payment', $paymentData);

            // Simulate API call to payment gateway
            $response = $this->callPaymentGateway($paymentData);

            // Log successful payment
            Log::info('Payment processed successfully', [
                'order_id' => $order->getId(),
                'transaction_id' => $response['transaction_id']
            ]);

            return [
                'success' => true,
                'transaction_id' => $response['transaction_id'],
                'status' => 'completed',
                'amount' => $amount->toArray(),
                'processed_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'order_id' => $order->getId(),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed'
            ];
        }
    }

    /**
     * Process a refund for a payment
     */
    public function processRefund(string $transactionId, MoneyDTO $amount, string $reason = ''): array
    {
        try {
            $refundData = [
                'transaction_id' => $transactionId,
                'amount' => $amount->getAmount(),
                'currency' => $amount->getCurrency(),
                'reason' => $reason,
                'timestamp' => time(),
                'test_mode' => $this->isTestMode
            ];

            Log::info('Processing refund', $refundData);

            // Simulate API call to payment gateway for refund
            $response = $this->callRefundGateway($refundData);

            Log::info('Refund processed successfully', [
                'transaction_id' => $transactionId,
                'refund_id' => $response['refund_id']
            ]);

            return [
                'success' => true,
                'refund_id' => $response['refund_id'],
                'status' => 'completed',
                'amount' => $amount->toArray(),
                'processed_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed'
            ];
        }
    }

    /**
     * Validate payment method
     */
    public function validatePaymentMethod(string $paymentType): bool
    {
        $validMethods = [
            'credit_card',
            'debit_card',
            'paypal',
            'bank_transfer',
            'apple_pay',
            'google_pay'
        ];

        return in_array($paymentType, $validMethods);
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $transactionId): array
    {
        try {
            // Simulate API call to check payment status
            $response = $this->callStatusCheck($transactionId);

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'status' => $response['status'],
                'last_updated' => $response['last_updated']
            ];
        } catch (\Exception $e) {
            Log::error('Payment status check failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Simulate payment gateway API call
     */
    private function callPaymentGateway(array $data): array
    {
        // In a real implementation, this would make an HTTP request to a payment gateway
        // For demonstration purposes, we'll simulate the response

        if ($this->isTestMode) {
            // Test mode - always succeed
            return [
                'transaction_id' => 'test_txn_' . uniqid(),
                'status' => 'success',
                'message' => 'Test payment processed'
            ];
        }

        // Simulate real payment processing with some randomness
        $successRate = 0.95; // 95% success rate

        if (rand(1, 100) <= ($successRate * 100)) {
            return [
                'transaction_id' => 'txn_' . uniqid(),
                'status' => 'success',
                'message' => 'Payment processed successfully'
            ];
        } else {
            throw new \Exception('Payment gateway temporarily unavailable');
        }
    }

    /**
     * Simulate refund gateway API call
     */
    private function callRefundGateway(array $data): array
    {
        if ($this->isTestMode) {
            return [
                'refund_id' => 'test_ref_' . uniqid(),
                'status' => 'success',
                'message' => 'Test refund processed'
            ];
        }

        return [
            'refund_id' => 'ref_' . uniqid(),
            'status' => 'success',
            'message' => 'Refund processed successfully'
        ];
    }

    /**
     * Simulate payment status check API call
     */
    private function callStatusCheck(string $transactionId): array
    {
        $statuses = ['pending', 'processing', 'completed', 'failed', 'cancelled'];

        return [
            'status' => $statuses[array_rand($statuses)],
            'last_updated' => now()->subMinutes(rand(1, 60))->toISOString()
        ];
    }

    /**
     * Get supported payment methods
     */
    public function getSupportedPaymentMethods(): array
    {
        return [
            'credit_card' => [
                'name' => 'Credit Card',
                'currencies' => ['USD', 'EUR', 'GBP'],
                'processing_time' => 'instant'
            ],
            'debit_card' => [
                'name' => 'Debit Card',
                'currencies' => ['USD', 'EUR', 'GBP'],
                'processing_time' => 'instant'
            ],
            'paypal' => [
                'name' => 'PayPal',
                'currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'],
                'processing_time' => 'instant'
            ],
            'bank_transfer' => [
                'name' => 'Bank Transfer',
                'currencies' => ['USD', 'EUR', 'GBP'],
                'processing_time' => '1-3 business days'
            ]
        ];
    }
}
