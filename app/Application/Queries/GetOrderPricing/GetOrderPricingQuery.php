<?php

namespace App\Application\Queries\GetOrderPricing;

use App\Domain\Order\ValueObjects\OrderId;

final class GetOrderPricingQuery
{
    public function __construct(
        public readonly OrderId $orderId
    ) {}
}
