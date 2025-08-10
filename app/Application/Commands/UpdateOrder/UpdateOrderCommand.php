<?php

namespace App\Application\Commands\UpdateOrder;

final class UpdateOrderCommand
{
    public function __construct(
        public int $orderId,
        public array $items,
        public ?string $customerName = null
    ) {}
}
