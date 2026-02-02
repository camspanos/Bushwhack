<?php

namespace App\Notifications;

use App\Models\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessful extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public PaymentTransaction $transaction
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Successful - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your payment has been processed successfully.')
            ->line('Amount: ' . $this->transaction->formatted_amount)
            ->line('Date: ' . $this->transaction->created_at->format('F j, Y'))
            ->action('View Subscription', url('/settings/subscription'))
            ->line('Thank you for being a premium member!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'type' => 'payment_successful',
        ];
    }
}

