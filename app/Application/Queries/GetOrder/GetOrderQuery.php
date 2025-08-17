<?php

namespace App\Application\Queries\GetOrder;

use App\Domain\Order\ValueObjects\OrderId;

final class GetOrderQuery
{
    public function __construct(public OrderId $orderId) {}
}