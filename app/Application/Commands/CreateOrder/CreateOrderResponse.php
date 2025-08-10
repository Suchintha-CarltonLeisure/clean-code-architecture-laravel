<?php

namespace App\Application\Commands\CreateOrder;

final class CreateOrderResponse
{
    public function __construct(
        public int $orderId,
        public float $totalPrice
    ) {}

    public function toArray(): array
    {
        return ['order_id' => $this->orderId, 'total_price' => $this->totalPrice];
    }
}
