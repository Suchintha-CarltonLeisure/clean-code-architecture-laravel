<?php

namespace App\Application\Queries\GetOrder;

use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\Entities\Order;

final class GetOrderHandler
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function handle(GetOrderQuery $query): ?Order
    {
        return $this->orders->findById($query->orderId);
    }
}