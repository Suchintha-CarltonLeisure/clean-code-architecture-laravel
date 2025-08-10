<?php

namespace App\Application\Commands\DeleteOrder;

use App\Domain\Repositories\OrderRepositoryInterface;

final class DeleteOrderHandler
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function handle(DeleteOrderCommand $cmd): bool
    {
        return $this->orders->deleteById($cmd->orderId);
    }
}
