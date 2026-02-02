<?php

namespace App\Notifications;

use App\Models\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PaymentTransaction $transaction
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Failed - Action Required')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We were unable to process your subscription payment.')
            ->line('Amount: ' . $this->transaction->formatted_amount)
            ->line('Reason: ' . ($this->transaction->failure_reason ?? 'Unknown error'))
            ->line('Please update your payment method to continue enjoying premium features.')
            ->action('Update Payment Method', url('/settings/subscription'))
            ->line('If you have any questions, please contact our support team.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'type' => 'payment_failed',
        ];
    }
}

