<?php

/**
 * Simple Domain Service Example
 * 
 * This demonstrates the core concept: Domain Services handle complex business logic
 * that doesn't belong to entities or value objects.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\ValueObjects\CustomerName;
use App\Domain\Order\Services\OrderPricingService;
use App\Application\DTOs\MoneyDTO;

echo "=== Simple Domain Service Example ===\n\n";

try {
    // 1. Create a simple order
    echo "1. Creating Order...\n";
    $laptop = new OrderItem('Gaming Laptop', 'LAP-001', 1, new MoneyDTO(1299.99));
    $mouse = new OrderItem('Wireless Mouse', 'MOU-001', 3, new MoneyDTO(49.99));

    $customerName = CustomerName::fromString('John Doe');
    $order = new Order([$laptop, $mouse], $customerName);

    echo "✅ Order created: " . $order->getCustomerName()->getValue() . "\n";
    echo "   Items: " . $order->getItemCount() . "\n";
    echo "   Base Total: " . $order->totalPrice()->format() . "\n\n";

    // 2. Use the Domain Service for pricing logic
    echo "2. Using Domain Service for Pricing Logic...\n";
    $pricingService = new OrderPricingService();

    // Calculate volume discount (business rule: 15% off for orders over $1000)
    $volumeDiscount = $pricingService->calculateVolumeDiscount($order);
    echo "   Volume Discount: " . $volumeDiscount->format() . "\n";

    // Calculate bulk discount (business rule: 5% off when buying 5+ of same item)
    $bulkDiscount = $pricingService->calculateBulkItemDiscount($order);
    echo "   Bulk Discount: " . $bulkDiscount->format() . "\n";

    // Calculate final price with all discounts
    $finalPrice = $pricingService->calculateFinalPrice($order);
    echo "   Final Price: " . $finalPrice->format() . "\n\n";

    // 3. Explain WHY we use Domain Services
    echo "3. Why Domain Services?\n";
    echo "   ❌ This logic doesn't belong in Order entity (not about identity/lifecycle)\n";
    echo "   ❌ This logic doesn't belong in OrderItem entity (involves multiple items)\n";
    echo "   ❌ This logic doesn't belong in MoneyDTO (not about money representation)\n";
    echo "   ✅ This logic belongs in Domain Service (complex business rules)\n\n";

    // 4. Show the business rules
    echo "4. Business Rules Encapsulated:\n";
    echo "   📊 Volume Discount: 10% off orders over $500, 15% off over $1000\n";
    echo "   📦 Bulk Discount: 5% off when buying 5+ of the same item\n";
    echo "   🧮 Final Price: Base total minus all applicable discounts\n\n";

    echo "=== Domain Service Concept Understood! ===\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
