<?php

namespace App\Application\Commands\CreateOrder;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;

final class CreateOrderHandler
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function handle(CreateOrderCommand $command): CreateOrderResponse
    {
        $order = new Order($command->items, $command->customerName);
        $saved = $this->orders->save($order);

        return new CreateOrderResponse($saved->getId()->getValue(), $saved->totalPrice());
    }
}