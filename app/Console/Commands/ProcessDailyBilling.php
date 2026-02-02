<?php

namespace App\Console\Commands;

use App\Contracts\PaymentGateway;
use App\Models\PaymentBatch;
use App\Models\PaymentBatchItem;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Notifications\PaymentFailed;
use App\Notifications\PaymentSuccessful;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDailyBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:process-daily {--dry-run : Run without actually processing payments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process daily billing for subscriptions due for renewal';

    public function __construct(
        protected PaymentGateway $paymentGateway
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info('Starting daily billing process...');

        // Create a new payment batch
        $batch = PaymentBatch::create([
            'batch_id' => PaymentBatch::generateBatchId(),
            'run_date' => now()->toDateString(),
            'status' => PaymentBatch::STATUS_PENDING,
        ]);

        $this->info("Created batch: {$batch->batch_id}");

        // Get all subscriptions due for billing
        $subscriptions = Subscription::dueForBilling()
            ->with(['user', 'plan'])
            ->get();

        $this->info("Found {$subscriptions->count()} subscriptions due for billing");

        if ($subscriptions->isEmpty()) {
            $batch->complete();
            $this->info('No subscriptions to process. Batch completed.');
            return Command::SUCCESS;
        }

        $batch->start();

        $processed = 0;
        $successful = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            $this->processSubscription($subscription, $batch, $dryRun, $processed, $successful, $failed);
        }

        // Update batch statistics
        $batch->updateStats();
        $batch->complete();

        $this->info("Billing complete. Processed: {$processed}, Successful: {$successful}, Failed: {$failed}");

        return Command::SUCCESS;
    }

    /**
     * Process a single subscription billing.
     */
    protected function processSubscription(
        Subscription $subscription,
        PaymentBatch $batch,
        bool $dryRun,
        int &$processed,
        int &$successful,
        int &$failed
    ): void {
        $user = $subscription->user;
        $plan = $subscription->plan;
        $amount = $plan->price;

        $this->line("Processing subscription #{$subscription->id} for user #{$user->id} ({$user->email})");

        // Create batch item
        $batchItem = PaymentBatchItem::create([
            'payment_batch_id' => $batch->id,
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'status' => PaymentBatchItem::STATUS_PENDING,
        ]);

        $processed++;

        if ($dryRun) {
            $this->line("  [DRY RUN] Would charge {$plan->formatted_price}");
            $batchItem->update(['status' => PaymentBatchItem::STATUS_SUCCEEDED]);
            $successful++;
            return;
        }

        try {
            // Process payment through gateway
            $result = $this->paymentGateway->processPayment($subscription, $amount);

            // Create transaction record
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'type' => PaymentTransaction::TYPE_CHARGE,
                'amount' => $amount,
                'currency' => 'USD',
                'status' => $result['success'] ? PaymentTransaction::STATUS_SUCCEEDED : PaymentTransaction::STATUS_FAILED,
                'external_id' => $result['transaction_id'],
                'failure_reason' => $result['error'],
            ]);

            if ($result['success']) {
                $this->handleSuccessfulPayment($subscription, $batchItem, $transaction, $plan);
                $successful++;
            } else {
                $this->handleFailedPayment($subscription, $batchItem, $transaction, $result['error']);
                $failed++;
            }
        } catch (\Exception $e) {
            Log::error("Billing error for subscription #{$subscription->id}: " . $e->getMessage());
            $batchItem->markFailed($e->getMessage());
            $failed++;
        }
    }

    /**
     * Handle a successful payment.
     */
    protected function handleSuccessfulPayment(
        Subscription $subscription,
        PaymentBatchItem $batchItem,
        PaymentTransaction $transaction,
        $plan
    ): void {
        // Update subscription next billing date
        $nextBillingDate = match ($plan->billing_interval) {
            'month' => now()->addMonths($plan->billing_interval_count),
            'year' => now()->addYears($plan->billing_interval_count),
            default => now()->addMonth(),
        };

        $subscription->update([
            'next_billing_date' => $nextBillingDate,
            'ends_at' => $nextBillingDate,
        ]);

        $batchItem->markSuccessful($transaction);

        // Send success notification
        $subscription->user->notify(new PaymentSuccessful($transaction));

        $this->info("  Payment successful: {$plan->formatted_price}");
    }

    /**
     * Handle a failed payment.
     */
    protected function handleFailedPayment(
        Subscription $subscription,
        PaymentBatchItem $batchItem,
        PaymentTransaction $transaction,
        ?string $error
    ): void {
        // Update subscription status to past_due
        $subscription->update([
            'status' => Subscription::STATUS_PAST_DUE,
        ]);

        $batchItem->markFailed($error ?? 'Payment failed', $transaction);

        // Send failure notification
        $subscription->user->notify(new PaymentFailed($transaction));

        $this->error("  Payment failed: {$error}");
    }
}

