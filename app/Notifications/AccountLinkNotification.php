<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountLinkNotification extends Notification
{
    public function __construct(public string $url, public bool $invitation = false) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject($this->invitation ? 'Your Achilles admin invitation' : 'Verify your Achilles registration')
            ->view('emails.account-link', ['url' => $this->url, 'invitation' => $this->invitation]);
    }
}
