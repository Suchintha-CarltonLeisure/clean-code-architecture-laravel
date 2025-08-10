<?php

namespace App\Application\Commands\CreateOrder;

final class CreateOrderCommand
{
    public function __construct(
        public array $items,
        public string $customerName
    ) {}
}
