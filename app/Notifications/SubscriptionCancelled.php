<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Subscription $subscription
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Subscription Cancelled - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your subscription has been cancelled.');

        if ($this->subscription->ends_at) {
            $message->line('You will continue to have access to premium features until ' . $this->subscription->ends_at->format('F j, Y') . '.');
        }

        return $message
            ->line('We\'re sorry to see you go! If you change your mind, you can resubscribe at any time.')
            ->action('Resubscribe', url('/settings/subscription'))
            ->line('Thank you for being a member.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'type' => 'subscription_cancelled',
        ];
    }
}

