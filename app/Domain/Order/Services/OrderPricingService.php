<?php

namespace App\Domain\Order\Services;

use App\Domain\Order\Entities\Order;
use App\Application\DTOs\MoneyDTO;

/**
 * Domain Service for pricing calculations
 * 
 * WHY Domain Service? This business logic doesn't belong to:
 * - Order entity (it's not about order identity or lifecycle)
 * - OrderItem entity (it involves multiple items and external rules)
 * - Value Objects (it's complex calculation logic)
 * 
 * This service encapsulates business rules about pricing that
 * are too complex for entities to handle alone.
 */
final class OrderPricingService
{
    /**
     * Calculate volume discount based on order total
     * Business Rule: 10% off for orders over $500, 15% off for orders over $1000
     */
    public function calculateVolumeDiscount(Order $order): MoneyDTO
    {
        $total = $order->totalPrice();
        $amount = $total->getAmount();

        if ($amount >= 1000) {
            return $total->multiply(0.15); // 15% discount
        }

        if ($amount >= 500) {
            return $total->multiply(0.10); // 10% discount
        }

        return new MoneyDTO(0); // No discount
    }

    /**
     * Calculate bulk item discount
     * Business Rule: 5% off when buying 5+ of the same item
     */
    public function calculateBulkItemDiscount(Order $order): MoneyDTO
    {
        $discount = new MoneyDTO(0);

        foreach ($order->getItems() as $item) {
            if ($item->getQuantity() >= 5) {
                $itemDiscount = $item->getTotalPrice()->multiply(0.05);
                $discount = $discount->add($itemDiscount);
            }
        }

        return $discount;
    }

    /**
     * Calculate final price with all discounts applied
     */
    public function calculateFinalPrice(Order $order): MoneyDTO
    {
        $baseTotal = $order->totalPrice();
        $volumeDiscount = $this->calculateVolumeDiscount($order);
        $bulkDiscount = $this->calculateBulkItemDiscount($order);

        $totalDiscount = $volumeDiscount->add($bulkDiscount);

        return $baseTotal->subtract($totalDiscount);
    }
}
