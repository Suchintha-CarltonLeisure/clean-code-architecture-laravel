<?php

namespace App\Application\Queries\GetOrder;

final class GetOrderQuery
{
    public function __construct(public int $orderId) {}
}
