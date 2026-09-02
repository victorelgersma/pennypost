<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ?string $name,
        protected ?string $email,
        protected string $message,
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('New Penny Post feedback')
            ->line('From: '.($this->name ?: '(no name given)'))
            ->line('Email: '.($this->email ?: '(no email given)'))
            ->line('Message:')
            ->line($this->message);

        // Lets you hit "reply" in your inbox and go straight back to them,
        // without storing their address anywhere.
        if ($this->email) {
            $mail->replyTo($this->email, $this->name ?: null);
        }

        return $mail;
    }
}
