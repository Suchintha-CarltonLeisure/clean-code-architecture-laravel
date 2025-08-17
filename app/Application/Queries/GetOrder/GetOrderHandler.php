<?php

namespace App\Application\Queries\GetOrder;

use App\Domain\Order\Repositories\OrderRepositoryInterface;

final class GetOrderHandler
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function handle(GetOrderQuery $query): ?GetOrderResponse
    {
        $order = $this->orders->findById($query->orderId);
        if (!$order) return null;
        return new GetOrderResponse($order->toArray());
    }
}