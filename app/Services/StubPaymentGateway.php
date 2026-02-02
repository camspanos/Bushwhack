<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Str;

class StubPaymentGateway implements PaymentGateway
{
    /**
     * Simulate failure rate (0-100). Set to 0 for always success.
     */
    protected int $failureRate = 0;

    /**
     * Set the failure rate for testing.
     */
    public function setFailureRate(int $rate): void
    {
        $this->failureRate = max(0, min(100, $rate));
    }

    /**
     * Determine if this payment should fail (for testing).
     */
    protected function shouldFail(): bool
    {
        return rand(1, 100) <= $this->failureRate;
    }

    /**
     * Generate a fake transaction ID.
     */
    protected function generateTransactionId(): string
    {
        return 'stub_txn_' . Str::random(24);
    }

    /**
     * Generate a fake session ID.
     */
    protected function generateSessionId(): string
    {
        return 'stub_sess_' . Str::random(24);
    }

    /**
     * {@inheritdoc}
     */
    public function createCheckoutSession(
        User $user,
        SubscriptionPlan $plan,
        string $successUrl,
        string $cancelUrl
    ): array {
        $sessionId = $this->generateSessionId();

        // In a real implementation, this would redirect to the payment gateway
        // For the stub, we'll return a URL that simulates successful checkout
        return [
            'session_id' => $sessionId,
            'checkout_url' => $successUrl . '?session_id=' . $sessionId,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function processPayment(Subscription $subscription, int $amount): array
    {
        if ($this->shouldFail()) {
            return [
                'success' => false,
                'transaction_id' => null,
                'error' => 'Payment declined: Insufficient funds (simulated failure)',
            ];
        }

        return [
            'success' => true,
            'transaction_id' => $this->generateTransactionId(),
            'error' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function cancelSubscription(Subscription $subscription): array
    {
        // Stub always succeeds for cancellation
        return [
            'success' => true,
            'error' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function refundPayment(string $transactionId, ?int $amount = null): array
    {
        if ($this->shouldFail()) {
            return [
                'success' => false,
                'refund_id' => null,
                'error' => 'Refund failed: Transaction not found (simulated failure)',
            ];
        }

        return [
            'success' => true,
            'refund_id' => 'stub_refund_' . Str::random(24),
            'error' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        // Stub always returns true for webhook verification
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function parseWebhookEvent(string $payload): array
    {
        $data = json_decode($payload, true) ?? [];

        return [
            'type' => $data['type'] ?? 'unknown',
            'data' => $data['data'] ?? [],
        ];
    }
}

