<?php

namespace App\Infrastructure\Repositories;

use App\Application\DTOs\MoneyDTO;
use App\Models\Order as EloquentOrder;
use App\Domain\Order\Entities\Order as DomainOrder;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\CustomerName;
use App\Domain\Order\ValueObjects\OrderStatus;

final class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function save(DomainOrder $order): DomainOrder
    {
        if ($order->getId()->isNotNull()) {
            $elo = EloquentOrder::find($order->getId()->getValue());
            if (!$elo) {
                // treat as create if not found
                $elo = new EloquentOrder();
            }
        } else {
            $elo = new EloquentOrder();
        }

        $elo->customer_name = $order->getCustomerName()->getValue();
        $elo->items = $order->getItems();
        $elo->total_price = $order->totalPrice();
        $elo->status = $order->getStatus()->getValue();
        $elo->save();

        $order->setId(OrderId::fromInt($elo->id));
        return $order;
    }

    public function findById(OrderId $id): ?DomainOrder
    {
        $elo = EloquentOrder::find($id->getValue());
        if (!$elo) return null;

        return new DomainOrder(
            $elo->items,
            CustomerName::fromString($elo->customer_name),
            OrderStatus::fromString($elo->status),
            OrderId::fromInt($elo->id)
        );
    }

    public function deleteById(OrderId $id): bool
    {
        $elo = EloquentOrder::find($id->getValue());
        if (!$elo) return false;
        return (bool)$elo->delete();
    }

    public function list(int $perPage = 15, int $page = 1): array
    {
        $skip = ($page - 1) * $perPage;
        $el = EloquentOrder::query()->orderBy('id', 'desc')->skip($skip)->take($perPage)->get();
        return $el->map(function ($e) {
            $order = new DomainOrder(
                $e->items,
                CustomerName::fromString($e->customer_name),
                OrderStatus::fromString($e->status),
                OrderId::fromInt($e->id)
            );
            return $order->toArray();
        })->all();
    }

    public function findByTotalPriceRange(MoneyDTO $minPrice, MoneyDTO $maxPrice): array
    {
        return EloquentOrder::whereBetween('total_price', [$minPrice->getAmount(), $maxPrice->getAmount()])
            ->get()
            ->map(function ($e) {
                $order = new DomainOrder(
                    $e->items,
                    CustomerName::fromString($e->customer_name),
                    OrderStatus::fromString($e->status),
                    OrderId::fromInt($e->id)
                );
                return $order->toArray();
            })
            ->all();
    }
}