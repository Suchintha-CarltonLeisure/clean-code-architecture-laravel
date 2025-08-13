<?php

namespace App\Infrastructure\Repositories;

use App\Application\DTOs\MoneyDTO;
use App\Models\Order as EloquentOrder;
use App\Domain\Models\Order\Order as DomainOrder;
use App\Domain\Repositories\OrderRepositoryInterface;

final class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function save(DomainOrder $order): DomainOrder
    {
        if ($order->getId()) {
            $elo = EloquentOrder::find($order->getId());
            if (!$elo) {
                // treat as create if not found
                $elo = new EloquentOrder();
            }
        } else {
            $elo = new EloquentOrder();
        }

        $elo->customer_name = $order->getCustomerName();
        $elo->items = $order->getItems();
        $elo->total_price = $order->totalPrice();
        $elo->status = $order->getStatus();
        $elo->save();

        $order->setId($elo->id);
        return $order;
    }

    public function findById(int $id): ?DomainOrder
    {
        $elo = EloquentOrder::find($id);
        if (!$elo) return null;
        return new DomainOrder($elo->items, $elo->customer_name, $elo->status, $elo->id);
    }

    public function deleteById(int $id): bool
    {
        $elo = EloquentOrder::find($id);
        if (!$elo) return false;
        return (bool)$elo->delete();
    }

    public function list(int $perPage = 15, int $page = 1): array
    {
        $skip = ($page - 1) * $perPage;
        $el = EloquentOrder::query()->orderBy('id', 'desc')->skip($skip)->take($perPage)->get();
        return $el->map(fn($e) => (new DomainOrder($e->items, $e->customer_name, $e->status, $e->id))->toArray())->all();
    }

    public function findByTotalPriceRange(MoneyDTO $minPrice, MoneyDTO $maxPrice): array
    {
        return EloquentOrder::whereBetween('total_price', [$minPrice->amount, $maxPrice->amount])
            ->get()
            ->toArray();
    }
}
