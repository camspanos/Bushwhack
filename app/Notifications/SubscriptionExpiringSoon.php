<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
        public int $daysUntilExpiry = 7
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiryDate = $this->subscription->ends_at?->format('F j, Y') ?? 'soon';

        return (new MailMessage)
            ->subject('Your Subscription is Expiring Soon')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("Your premium subscription will expire in {$this->daysUntilExpiry} days on {$expiryDate}.")
            ->line('Renew now to continue enjoying all premium features without interruption.')
            ->action('Renew Subscription', url('/settings/subscription'))
            ->line('Thank you for being a valued member!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'type' => 'subscription_expiring_soon',
            'days_until_expiry' => $this->daysUntilExpiry,
        ];
    }
}

