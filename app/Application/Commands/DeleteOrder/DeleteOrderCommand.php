<?php

namespace App\Application\Commands\DeleteOrder;

final class DeleteOrderCommand
{
    public function __construct(public int $orderId) {}
}
