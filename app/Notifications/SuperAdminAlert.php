<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * A system event worth the Super Admin's attention. Stored in the database
 * (in-app notification centre) and mirrored to registered phones by
 * App\Services\SuperAdminNotifier.
 */
class SuperAdminAlert extends Notification
{
    public function __construct(
        public string $alertType,
        public string $title,
        public string $body,
        public ?string $route = null,
        public array $meta = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    public function payload(): array
    {
        return [
            'type' => $this->alertType,
            'title' => $this->title,
            'body' => $this->body,
            'route' => $this->route,
            'meta' => $this->meta,
        ];
    }
}