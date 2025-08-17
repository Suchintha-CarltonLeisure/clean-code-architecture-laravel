<?php

namespace App\Application\Commands\UpdateOrder;

use App\Domain\Order\ValueObjects\OrderId;

final class UpdateOrderCommand
{
    public function __construct(
        public OrderId $orderId,
        public array $items,
        public ?string $customerName = null
    ) {}
}