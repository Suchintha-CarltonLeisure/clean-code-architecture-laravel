<?php

namespace App\Domain\Models\Order;

use App\Application\DTOs\MoneyDTO;
use App\Domain\Models\Order\Exceptions\OrderModificationException;

class Order
{
    private ?int $id = null;
    private string $customerName;
    private array $items = [];
    private string $status;

    public function __construct(array $items, string $customerName, ?string $status = 'pending', ?int $id = null)
    {
        if (empty($items)) {
            throw new OrderModificationException("Order must contain at least one item.");
        }

        $this->items = $items;
        $this->customerName = $customerName;
        $this->status = $status;
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(?int $id): void
    {
        $this->id = $id;
    }
    public function getCustomerName(): string
    {
        return $this->customerName;
    }
    public function getItems(): array
    {
        return $this->items;
    }
    public function getStatus(): string
    {
        return $this->status;
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
            'id' => $this->id,
            'customer_name' => $this->customerName,
            'items' => $this->items,
            'total_price' => $this->totalPrice(),
            'status' => $this->status,
        ];
    }
}
