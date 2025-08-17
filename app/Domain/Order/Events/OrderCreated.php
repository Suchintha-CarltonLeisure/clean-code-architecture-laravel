<?php

namespace App\Domain\Order\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\CustomerName;
use App\Application\DTOs\MoneyDTO;
use DateTimeImmutable;

final class OrderCreated implements DomainEvent
{
    public function __construct(
        private OrderId $orderId,
        private CustomerName $customerName,
        private MoneyDTO $totalAmount,
        private int $itemCount,
        private DateTimeImmutable $occurredOn
    ) {}

    public function getAggregateId(): string
    {
        return (string) $this->orderId->getValue();
    }

    public function getEventName(): string
    {
        return 'order.created';
    }

    public function getOccurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function getEventData(): array
    {
        return [
            'order_id' => $this->orderId->getValue(),
            'customer_name' => $this->customerName->getValue(),
            'customer_first_name' => $this->customerName->getFirstName(),
            'customer_last_name' => $this->customerName->getLastName(),
            'total_amount' => $this->totalAmount->toArray(),
            'item_count' => $this->itemCount,
            'occurred_on' => $this->occurredOn->format('Y-m-d H:i:s')
        ];
    }

    // Getters for event handlers
    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }

    public function getCustomerName(): CustomerName
    {
        return $this->customerName;
    }

    public function getTotalAmount(): MoneyDTO
    {
        return $this->totalAmount;
    }

    public function getItemCount(): int
    {
        return $this->itemCount;
    }
}
