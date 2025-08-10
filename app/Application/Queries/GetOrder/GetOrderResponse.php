<?php

namespace App\Application\Queries\GetOrder;

final class GetOrderResponse
{
    public function __construct(public array $order) {}
    public function toArray(): array
    {
        return $this->order;
    }
}
