<?php

namespace App\Application\Queries\GetOrderPricing;

use App\Application\DTOs\MoneyDTO;

final class GetOrderPricingResponse
{
    public function __construct(
        public readonly int $orderId,
        public readonly MoneyDTO $baseTotal,
        public readonly MoneyDTO $volumeDiscount,
        public readonly MoneyDTO $bulkItemDiscount,
        public readonly MoneyDTO $totalDiscount,
        public readonly MoneyDTO $finalPrice
    ) {}

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'pricing' => [
                'base_total' => $this->baseTotal->toArray(),
                'discounts' => [
                    'volume_discount' => $this->volumeDiscount->toArray(),
                    'bulk_item_discount' => $this->bulkItemDiscount->toArray(),
                    'total_discount' => $this->totalDiscount->toArray(),
                ],
                'final_price' => $this->finalPrice->toArray(),
            ],
            'discount_rules' => [
                'volume_discount' => '10% off for orders over $500, 15% off for orders over $1000',
                'bulk_item_discount' => '5% off when buying 5+ of the same item'
            ]
        ];
    }
}
