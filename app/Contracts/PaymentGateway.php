<?php

namespace App\Contracts;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;

interface PaymentGateway
{
    /**
     * Create a checkout session for a subscription plan.
     *
     * @param User $user
     * @param SubscriptionPlan $plan
     * @param string $successUrl
     * @param string $cancelUrl
     * @return array{session_id: string, checkout_url: string}
     */
    public function createCheckoutSession(
        User $user,
        SubscriptionPlan $plan,
        string $successUrl,
        string $cancelUrl
    ): array;

    /**
     * Process a payment for a subscription.
     *
     * @param Subscription $subscription
     * @param int $amount Amount in cents
     * @return array{success: bool, transaction_id: string|null, error: string|null}
     */
    public function processPayment(Subscription $subscription, int $amount): array;

    /**
     * Cancel a subscription in the payment gateway.
     *
     * @param Subscription $subscription
     * @return array{success: bool, error: string|null}
     */
    public function cancelSubscription(Subscription $subscription): array;

    /**
     * Refund a payment.
     *
     * @param string $transactionId
     * @param int|null $amount Amount in cents (null for full refund)
     * @return array{success: bool, refund_id: string|null, error: string|null}
     */
    public function refundPayment(string $transactionId, ?int $amount = null): array;

    /**
     * Verify a webhook signature.
     *
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * Parse a webhook event.
     *
     * @param string $payload
     * @return array{type: string, data: array}
     */
    public function parseWebhookEvent(string $payload): array;
}

