<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountDeletionNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $url)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirm deletion of your Penny Post account')
            ->line('We received a request to permanently delete your Penny Post account, including all your letters and drafts.')
            ->line('This link expires in 15 minutes.')
            ->action('Permanently delete my account', $this->url)
            ->line("If you didn't request this, you can safely ignore this email — your account is safe.");
    }
}
