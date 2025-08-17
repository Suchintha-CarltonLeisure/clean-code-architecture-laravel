<?php

namespace App\Domain\Order\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\OrderStatus;
use DateTimeImmutable;

final class OrderStatusChanged implements DomainEvent
{
    public function __construct(
        private OrderId $orderId,
        private OrderStatus $previousStatus,
        private OrderStatus $newStatus,
        private DateTimeImmutable $occurredOn
    ) {}

    public function getAggregateId(): string
    {
        return (string) $this->orderId->getValue();
    }

    public function getEventName(): string
    {
        return 'order.status_changed';
    }

    public function getOccurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function getEventData(): array
    {
        return [
            'order_id' => $this->orderId->getValue(),
            'previous_status' => $this->previousStatus->getValue(),
            'new_status' => $this->newStatus->getValue(),
            'occurred_on' => $this->occurredOn->format('Y-m-d H:i:s')
        ];
    }

    // Getters for event handlers
    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }

    public function getPreviousStatus(): OrderStatus
    {
        return $this->previousStatus;
    }

    public function getNewStatus(): OrderStatus
    {
        return $this->newStatus;
    }
}
