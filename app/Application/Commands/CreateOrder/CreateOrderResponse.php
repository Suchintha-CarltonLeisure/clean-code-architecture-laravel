<?php

namespace App\Application\Commands\CreateOrder;

use App\Application\DTOs\MoneyDTO;

final class CreateOrderResponse
{
    public function __construct(
        public int $orderId,
        public readonly MoneyDTO $totalPrice
    ) {}

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'total_price' => $this->totalPrice->toArray(),
            'message' => 'Order created successfully'
        ];
    }
}
