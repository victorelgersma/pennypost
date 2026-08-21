<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginLinkNotification extends Notification
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
            ->subject('Your Penny Post login link')
            ->line("Click below to log in. This link expires in 15 minutes and can only be used once.")
            ->action('Log in to Penny Post', $this->url)
            ->line("If you didn't request this, you can safely ignore this email.");
    }
}
