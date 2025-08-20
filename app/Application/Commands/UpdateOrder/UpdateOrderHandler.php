<?php

namespace App\Application\Commands\UpdateOrder;

use App\Application\DTOs\MoneyDTO;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\CustomerName;

final class UpdateOrderHandler
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function handle(UpdateOrderCommand $cmd): ?array
    {
        $order = $this->orders->findById($cmd->orderId);

        if (!$order) return null;

        // Convert raw item data to OrderItem entities
        $orderItems = [];
        foreach ($cmd->items as $itemData) {
            $orderItems[] = new OrderItem(
                productName: $itemData['product_name'],
                productSku: $itemData['product_sku'],
                quantity: $itemData['quantity'],
                unitPrice: new MoneyDTO($itemData['unit_price']),
                description: $itemData['description'] ?? null
            );
        }

        $order->updateItems($orderItems);

        // Update customer name if provided
        // if ($cmd->customerName) {
        //     $order->updateCustomerName(new CustomerName($cmd->customerName));
        // }

        $updated = $this->orders->save($order);

        return $updated->toArray();
    }
}
