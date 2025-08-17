<?php

namespace App\Domain\Order\Entities;

use App\Application\DTOs\MoneyDTO;
use App\Domain\Order\ValueObjects\OrderItemId;

final class OrderItem
{
    private OrderItemId $id;
    private string $productName;
    private string $productSku;
    private int $quantity;
    private MoneyDTO $unitPrice;
    private ?string $description;

    public function __construct(
        string $productName,
        string $productSku,
        int $quantity,
        MoneyDTO $unitPrice,
        ?string $description = null,
        ?OrderItemId $id = null
    ) {
        if (empty(trim($productName))) {
            throw new \InvalidArgumentException('Product name cannot be empty');
        }

        if (empty(trim($productSku))) {
            throw new \InvalidArgumentException('Product SKU cannot be empty');
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero');
        }

        $this->productName = trim($productName);
        $this->productSku = trim($productSku);
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->description = $description ? trim($description) : null;
        $this->id = $id ?? OrderItemId::generate();
    }

    public function getId(): OrderItemId
    {
        return $this->id;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getProductSku(): string
    {
        return $this->productSku;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): MoneyDTO
    {
        return $this->unitPrice;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTotalPrice(): MoneyDTO
    {
        return $this->unitPrice->multiply($this->quantity);
    }

    public function updateQuantity(int $newQuantity): void
    {
        if ($newQuantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero');
        }
        $this->quantity = $newQuantity;
    }

    public function updateUnitPrice(MoneyDTO $newUnitPrice): void
    {
        $this->unitPrice = $newUnitPrice;
    }

    public function updateDescription(?string $newDescription): void
    {
        $this->description = $newDescription ? trim($newDescription) : null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'product_name' => $this->productName,
            'product_sku' => $this->productSku,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice->toArray(),
            'total_price' => $this->getTotalPrice()->toArray(),
            'description' => $this->description,
        ];
    }

    public function equals(OrderItem $other): bool
    {
        return $this->id->equals($other->id);
    }
}
