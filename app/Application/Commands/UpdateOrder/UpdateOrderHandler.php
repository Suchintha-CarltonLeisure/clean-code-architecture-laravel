<?php

namespace App\Application\Commands\UpdateOrder;

use App\Domain\Order\Repositories\OrderRepositoryInterface;

final class UpdateOrderHandler
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function handle(UpdateOrderCommand $cmd): ?array
    {
        $order = $this->orders->findById($cmd->orderId);
        if (!$order) return null;

        $order->updateItems($cmd->items);
        // optionally update customerName via a domain method (not shown)
        $updated = $this->orders->save($order);
        return $updated->toArray();
    }
}