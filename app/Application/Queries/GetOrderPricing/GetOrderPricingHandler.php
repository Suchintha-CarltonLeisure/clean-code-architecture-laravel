<?php

namespace App\Application\Queries\GetOrderPricing;

use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\Services\OrderPricingService;

final class GetOrderPricingHandler
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private OrderPricingService $pricingService
    ) {}

    public function handle(GetOrderPricingQuery $query): ?GetOrderPricingResponse
    {
        $order = $this->orderRepository->findById($query->orderId);
        
        if (!$order) {
            return null;
        }

        // Use Domain Service for complex pricing calculations
        $baseTotal = $order->totalPrice();
        $volumeDiscount = $this->pricingService->calculateVolumeDiscount($order);
        $bulkItemDiscount = $this->pricingService->calculateBulkItemDiscount($order);
        $totalDiscount = $volumeDiscount->add($bulkItemDiscount);
        $finalPrice = $this->pricingService->calculateFinalPrice($order);

        return new GetOrderPricingResponse(
            $order->getId()->getValue(),
            $baseTotal,
            $volumeDiscount,
            $bulkItemDiscount,
            $totalDiscount,
            $finalPrice
        );
    }
}
