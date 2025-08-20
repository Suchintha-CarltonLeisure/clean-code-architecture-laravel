<?php

namespace App\Domain\Order\Entities;

use App\Application\DTOs\MoneyDTO;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\Exceptions\OrderModificationException;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\OrderItemId;
use App\Domain\Order\ValueObjects\CustomerName;
use App\Domain\Order\ValueObjects\OrderStatus;

class Order
{
    private OrderId $id;
    private CustomerName $customerName;
    /** @var OrderItem[] */
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

        // Validate that all items are OrderItem instances
        foreach ($items as $item) {
            if (!$item instanceof OrderItem) {
                throw new OrderModificationException("All items must be OrderItem instances.");
            }
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

    /**
     * @return OrderItem[]
     */
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

    public function updateCustomerName(CustomerName $newCustomerName): void
    {
        $this->customerName = $newCustomerName;
    }

    public function totalPrice(): MoneyDTO
    {
        $total = new MoneyDTO(0);
        foreach ($this->items as $item) {
            $total = $total->add($item->getTotalPrice());
        }
        return $total;
    }

    public function updateItems(array $items): void
    {
        if (empty($items)) {
            throw new OrderModificationException("Order must contain at least one item.");
        }

        // Validate that all items are OrderItem instances
        foreach ($items as $item) {
            if (!$item instanceof OrderItem) {
                throw new OrderModificationException("All items must be OrderItem instances.");
            }
        }

        $this->items = $items;
    }

    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
    }

    public function removeItem(OrderItemId $itemId): void
    {
        $this->items = array_filter($this->items, function (OrderItem $item) use ($itemId) {
            return !$item->getId()->equals($itemId);
        });

        if (empty($this->items)) {
            throw new OrderModificationException("Cannot remove all items from an order.");
        }
    }

    public function findItem(OrderItemId $itemId): ?OrderItem
    {
        foreach ($this->items as $item) {
            if ($item->getId()->equals($itemId)) {
                return $item;
            }
        }
        return null;
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'customer_name' => $this->customerName->getValue(),
            'customer_first_name' => $this->customerName->getFirstName(),
            'customer_last_name' => $this->customerName->getLastName(),
            'customer_initials' => $this->customerName->getInitials(),
            'items' => array_map(fn(OrderItem $item) => $item->toArray(), $this->items),
            'total_price' => $this->totalPrice()->toArray(),
            'status' => $this->status->getValue(),
        ];
    }
}
