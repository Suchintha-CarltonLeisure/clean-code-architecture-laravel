<?php

namespace Tests\Unit;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Application\DTOs\MoneyDTO;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\CustomerName;
use App\Domain\Order\ValueObjects\OrderStatus;

class InMemoryOrderRepository implements OrderRepositoryInterface
{
    private array $store = [];
    private int $next = 1;

    public function save(Order $order): Order
    {
        if ($order->getId()->isNull()) {
            $order->setId(OrderId::fromInt($this->next++));
        }
        $this->store[$order->getId()->getValue()] = $order;
        return $order;
    }

    public function findById(OrderId $id): ?Order
    {
        return $this->store[$id->getValue()] ?? null;
    }

    public function deleteById(OrderId $id): bool
    {
        if (isset($this->store[$id->getValue()])) {
            unset($this->store[$id->getValue()]);
            return true;
        }
        return false;
    }

    public function list(int $perPage = 15, int $page = 1): array
    {
        return array_map(fn($o) => $o->toArray(), array_values($this->store));
    }

    public function findByTotalPriceRange(MoneyDTO $minPrice, MoneyDTO $maxPrice): array
    {
        return array_filter(
            array_map(fn($o) => $o->toArray(), array_values($this->store)),
            function ($order) use ($minPrice, $maxPrice) {
                $total = $order['total_price']['amount'] ?? 0;
                return $total >= $minPrice->getAmount() && $total <= $maxPrice->getAmount();
            }
        );
    }
}