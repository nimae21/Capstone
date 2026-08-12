<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Shipped => 'Shipped',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Statuses this status is allowed to move to.
     * This is the state machine — the single place that defines valid transitions.
     */
   public function allowedTransitions(): array
{
    return match($this) {
        // Pending -> Paid is handled exclusively by the PayMongo webhook
        // (OrderService::confirmPayment), never through the admin panel.
        self::Pending => [self::Cancelled],
        self::Paid => [self::Shipped, self::Cancelled],
        self::Shipped => [self::Completed],
        self::Completed => [],
        self::Cancelled => [],
    };
}

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isCancellable(): bool
    {
        return in_array($this, [self::Pending, self::Paid], true);
    }
}