<?php

namespace App\Application\Commands\DeleteOrder;

use App\Domain\Order\ValueObjects\OrderId;

final class DeleteOrderCommand
{
    public function __construct(public OrderId $orderId) {}
}