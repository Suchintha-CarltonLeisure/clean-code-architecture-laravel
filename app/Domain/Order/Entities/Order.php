<?php

namespace App\Domain\Order\Entities;

use App\Application\DTOs\MoneyDTO;
use App\Domain\Order\Exceptions\OrderModificationException;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\CustomerName;
use App\Domain\Order\ValueObjects\OrderStatus;

class Order
{
    private OrderId $id;
    private CustomerName $customerName;
    private array $items = [];
    private OrderStatus $status;

    public function __construct(
        array $items,
        CustomerName $customerName,
        ?OrderStatus $status = null,
        ?OrderId $id = null
    ) {
        if (empty($items)) {
            throw new OrderModificationException("Order must contain at least one item.");
        }

        $this->items = $items;
        $this->customerName = $customerName;
        $this->status = $status ?? OrderStatus::pending();
        $this->id = $id ?? OrderId::generate();
    }

    public function getId(): OrderId
    {
        return $this->id;
    }

    public function setId(OrderId $id): void
    {
        $this->id = $id;
    }

    public function getCustomerName(): CustomerName
    {
        return $this->customerName;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function updateStatus(OrderStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw new OrderModificationException(
                "Cannot transition from status '{$this->status->getValue()}' to '{$newStatus->getValue()}'"
            );
        }
        $this->status = $newStatus;
    }

    public function totalPrice(): MoneyDTO
    {
        $total = array_sum(array_map(fn($i) => (float)$i['price'], $this->items));
        return new MoneyDTO($total);
    }

    public function updateItems(array $items): void
    {
        if (empty($items)) {
            throw new OrderModificationException("Order must contain at least one item.");
        }
        $this->items = $items;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'customer_name' => $this->customerName->getValue(),
            'customer_first_name' => $this->customerName->getFirstName(),
            'customer_last_name' => $this->customerName->getLastName(),
            'customer_initials' => $this->customerName->getInitials(),
            'items' => $this->items,
            'total_price' => $this->totalPrice(),
            'status' => $this->status->getValue(),
        ];
    }
}
