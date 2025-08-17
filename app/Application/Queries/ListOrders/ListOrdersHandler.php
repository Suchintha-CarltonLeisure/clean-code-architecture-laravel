<?php

namespace App\Application\Queries\ListOrders;

use App\Domain\Order\Repositories\OrderRepositoryInterface;

final class ListOrdersHandler
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function handle(ListOrdersQuery $q): array
    {
        return $this->orders->list($q->perPage, $q->page);
    }
}