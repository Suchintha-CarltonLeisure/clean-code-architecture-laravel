<?php

namespace App\Application\Commands\CreateOrder;

use App\Domain\Models\Order\Order;
use App\Domain\Repositories\OrderRepositoryInterface;

final class CreateOrderHandler
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function handle(CreateOrderCommand $command): CreateOrderResponse
    {
        $order = new Order($command->items, $command->customerName);
        $order->setId(null); // ensure
        $saved = $this->orders->save($order);

        return new CreateOrderResponse($saved->getId(), $saved->totalPrice());
    }
}
