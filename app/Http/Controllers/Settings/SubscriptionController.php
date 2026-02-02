<?php

namespace App\Http\Controllers\Settings;

use App\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\PaymentTransaction;
use App\Notifications\SubscriptionCancelled;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        protected PaymentGateway $paymentGateway
    ) {}

    /**
     * Display the subscription settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $plans = SubscriptionPlan::active()->ordered()->get();
        $activeSubscription = $user->activeSubscription()->with('plan')->first();

        // Get recent transactions
        $recentTransactions = $user->paymentTransactions()
            ->with('subscription.plan')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return Inertia::render('settings/Subscription', [
            'plans' => $plans,
            'activeSubscription' => $activeSubscription,
            'recentTransactions' => $recentTransactions,
            'isPremium' => $user->isPremium(),
        ]);
    }

    /**
     * Initiate checkout for a subscription plan.
     */
    public function checkout(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $user = $request->user();

        // Check if user already has an active subscription
        if ($user->activeSubscription()->exists()) {
            return back()->withErrors([
                'subscription' => 'You already have an active subscription.',
            ]);
        }

        $successUrl = route('subscription.success');
        $cancelUrl = route('subscription.edit');

        $session = $this->paymentGateway->createCheckoutSession(
            $user,
            $plan,
            $successUrl,
            $cancelUrl
        );

        // For the stub gateway, we'll simulate immediate success
        // In production, this would redirect to the payment gateway
        return redirect($session['checkout_url']);
    }

    /**
     * Handle successful checkout callback.
     */
    public function success(Request $request): RedirectResponse
    {
        $user = $request->user();
        $sessionId = $request->query('session_id');

        // In production, verify the session with the payment gateway
        // For stub, we'll create the subscription directly

        // Get the plan from session or default to monthly
        $planSlug = $request->query('plan', 'monthly');
        $plan = SubscriptionPlan::where('slug', $planSlug)->first();

        if (!$plan) {
            $plan = SubscriptionPlan::active()->ordered()->first();
        }

        if (!$plan) {
            return redirect()->route('subscription.edit')
                ->withErrors(['subscription' => 'No subscription plans available.']);
        }

        // Create the subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => $plan->isLifetime() ? null : $this->calculateEndDate($plan),
            'next_billing_date' => $plan->isLifetime() ? null : $this->calculateNextBillingDate($plan),
            'external_id' => $sessionId,
        ]);

        // Create the initial payment transaction
        PaymentTransaction::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'type' => PaymentTransaction::TYPE_CHARGE,
            'amount' => $plan->price,
            'currency' => 'USD',
            'status' => PaymentTransaction::STATUS_SUCCEEDED,
            'payment_method' => 'card',
            'external_id' => 'stub_txn_' . uniqid(),
        ]);

        // Update user's is_premium flag for backwards compatibility
        $user->update(['is_premium' => true]);

        return redirect()->route('subscription.edit')
            ->with('success', 'Welcome to Premium! Your subscription is now active.');
    }

    /**
     * Cancel the user's subscription.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $user = $request->user();
        $subscription = $user->activeSubscription()->first();

        if (!$subscription) {
            return back()->withErrors([
                'subscription' => 'No active subscription found.',
            ]);
        }

        // Cancel in payment gateway
        $result = $this->paymentGateway->cancelSubscription($subscription);

        if (!$result['success']) {
            return back()->withErrors([
                'subscription' => $result['error'] ?? 'Failed to cancel subscription.',
            ]);
        }

        // Cancel the subscription locally
        $subscription->cancel($request->input('reason'));

        // Update user's is_premium flag for backwards compatibility
        $user->update(['is_premium' => false]);

        // Send cancellation notification
        $user->notify(new SubscriptionCancelled($subscription));

        return redirect()->route('subscription.edit')
            ->with('success', 'Your subscription has been cancelled.');
    }

    /**
     * Handle webhook events from payment gateway.
     */
    public function webhook(Request $request): \Illuminate\Http\JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Signature', '');

        if (!$this->paymentGateway->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $this->paymentGateway->parseWebhookEvent($payload);

        // Handle different event types
        match ($event['type']) {
            'payment.succeeded' => $this->handlePaymentSucceeded($event['data']),
            'payment.failed' => $this->handlePaymentFailed($event['data']),
            'subscription.cancelled' => $this->handleSubscriptionCancelled($event['data']),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    /**
     * Handle successful payment webhook.
     */
    protected function handlePaymentSucceeded(array $data): void
    {
        // Implementation for handling successful payment webhooks
        // This would update subscription status, create transaction records, etc.
    }

    /**
     * Handle failed payment webhook.
     */
    protected function handlePaymentFailed(array $data): void
    {
        // Implementation for handling failed payment webhooks
        // This would update subscription status, notify user, etc.
    }

    /**
     * Handle subscription cancelled webhook.
     */
    protected function handleSubscriptionCancelled(array $data): void
    {
        // Implementation for handling subscription cancellation webhooks
    }

    /**
     * Calculate the end date for a subscription.
     */
    protected function calculateEndDate(SubscriptionPlan $plan): \Carbon\Carbon
    {
        return match ($plan->billing_interval) {
            'month' => now()->addMonths($plan->billing_interval_count),
            'year' => now()->addYears($plan->billing_interval_count),
            default => now()->addMonth(),
        };
    }

    /**
     * Calculate the next billing date for a subscription.
     */
    protected function calculateNextBillingDate(SubscriptionPlan $plan): \Carbon\Carbon
    {
        return $this->calculateEndDate($plan);
    }
}

