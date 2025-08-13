<?php

namespace App\Domain\Repositories;

use App\Application\DTOs\MoneyDTO;
use App\Domain\Models\Order\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): Order;
    public function findById(int $id): ?Order;
    public function deleteById(int $id): bool;
    /** Simple list - adjust to return paginator if you want */
    public function list(int $perPage = 15, int $page = 1): array;

    public function findByTotalPriceRange(MoneyDTO $minPrice, MoneyDTO $maxPrice): array;
}
