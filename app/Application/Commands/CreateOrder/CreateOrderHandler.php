<?php

namespace App\Application\Commands\CreateOrder;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\Services\OrderPricingService;

final class CreateOrderHandler
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private OrderPricingService $pricingService
    ) {}

    public function handle(CreateOrderCommand $command): CreateOrderResponse
    {
        // Convert request data to OrderItem entities
        $orderItems = [];
        foreach ($command->items as $itemData) {
            $orderItems[] = new \App\Domain\Order\Entities\OrderItem(
                $itemData['product_name'],
                $itemData['product_sku'],
                $itemData['quantity'],
                new \App\Application\DTOs\MoneyDTO($itemData['unit_price']),
                $itemData['description'] ?? null
            );
        }

        $order = new Order($orderItems, $command->customerName);

        // Use Domain Service for pricing calculations
        $finalPrice = $this->pricingService->calculateFinalPrice($order);

        $saved = $this->orders->save($order);

        return new CreateOrderResponse($saved->getId()->getValue(), $saved->totalPrice());
    }
}
