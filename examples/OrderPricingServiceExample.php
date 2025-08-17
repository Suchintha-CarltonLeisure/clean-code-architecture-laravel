<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\ValueObjects\CustomerName;
use App\Domain\Order\Services\OrderPricingService;
use App\Application\DTOs\MoneyDTO;

echo "=== Order Pricing Service Example ===\n\n";

// Create order items to demonstrate different discount scenarios
$items = [
    // High-value item (triggers volume discount)
    new OrderItem(
        'MacBook Pro 16"',
        'MBP-16-001',
        1,
        new MoneyDTO(2499.99),
        'Apple MacBook Pro 16-inch with M3 Pro chip'
    ),
    
    // Bulk item (triggers bulk discount - 6 items)
    new OrderItem(
        'USB-C Cable',
        'USB-C-001',
        6,
        new MoneyDTO(29.99),
        'High-quality USB-C charging cable'
    ),
    
    // Regular item
    new OrderItem(
        'Magic Mouse 2',
        'MM-002',
        1,
        new MoneyDTO(79.99),
        'Wireless Magic Mouse 2'
    )
];

// Create order
$order = new Order(
    $items,
    CustomerName::fromString('Alice Johnson')
);

// Initialize Domain Service
$pricingService = new OrderPricingService();

echo "Order Details:\n";
echo "Customer: " . $order->getCustomerName()->getValue() . "\n";
echo "Items:\n";
foreach ($order->getItems() as $item) {
    echo sprintf(
        "  - %s (SKU: %s) x%d @ %s each = %s\n",
        $item->getProductName(),
        $item->getProductSku(),
        $item->getQuantity(),
        $item->getUnitPrice()->format(),
        $item->getTotalPrice()->format()
    );
}

echo "\n=== Pricing Calculations ===\n";

// Base total
$baseTotal = $order->totalPrice();
echo "Base Total: " . $baseTotal->format() . "\n";

// Volume discount calculation
$volumeDiscount = $pricingService->calculateVolumeDiscount($order);
echo "Volume Discount (15% for orders over $1000): " . $volumeDiscount->format() . "\n";

// Bulk item discount calculation
$bulkDiscount = $pricingService->calculateBulkItemDiscount($order);
echo "Bulk Item Discount (5% for 5+ items): " . $bulkDiscount->format() . "\n";

// Final price
$finalPrice = $pricingService->calculateFinalPrice($order);
$totalSavings = $baseTotal->subtract($finalPrice);

echo "\nFinal Summary:\n";
echo "Base Total: " . $baseTotal->format() . "\n";
echo "Total Savings: " . $totalSavings->format() . "\n";
echo "Final Price: " . $finalPrice->format() . "\n";

echo "\n=== Business Rules Demonstrated ===\n";
echo "✅ Volume Discount: 15% applied (order > $1000)\n";
echo "✅ Bulk Item Discount: 5% applied (USB-C Cable x6)\n";
echo "✅ Domain Service encapsulates complex pricing logic\n";
echo "✅ Business rules are centralized and testable\n";

echo "\n=== API Usage ===\n";
echo "Test the API endpoint:\n";
echo "GET /api/orders/{order_id}/pricing\n";
echo "This endpoint uses the same Domain Service through Clean Architecture layers.\n";
