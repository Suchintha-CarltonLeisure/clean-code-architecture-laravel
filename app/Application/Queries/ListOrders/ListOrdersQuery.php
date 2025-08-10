<?php

namespace App\Application\Queries\ListOrders;

final class ListOrdersQuery
{
    public function __construct(public int $perPage = 15, public int $page = 1) {}
}
