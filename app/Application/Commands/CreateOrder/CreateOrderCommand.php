<?php

namespace App\Application\Commands\CreateOrder;

use App\Domain\Order\ValueObjects\CustomerName;

final class CreateOrderCommand
{
    public function __construct(
        public array $items,
        public CustomerName $customerName
    ) {}
}
