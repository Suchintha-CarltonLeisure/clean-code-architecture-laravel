<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\DTOs\MoneyDTO;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\ValueObjects\CustomerName;
use App\Domain\Order\ValueObjects\OrderStatus;

// Example: Creating an Order with OrderItems
echo "=== Order with Items Example ===\n\n";

// Create customer name
$customerName = CustomerName::fromString('John Doe');

// Create order items
$item1 = new OrderItem(
    'Laptop',
    'LAP-001',
    1,
    new MoneyDTO(999.99),
    'High-performance gaming laptop'
);

$item2 = new OrderItem(
    'Mouse',
    'MOU-001',
    2,
    new MoneyDTO(29.99),
    'Wireless gaming mouse'
);

$item3 = new OrderItem(
    'Keyboard',
    'KEY-001',
    1,
    new MoneyDTO(89.99),
    'Mechanical gaming keyboard'
);

// Create order with items
$order = new Order(
    [$item1, $item2, $item3],
    $customerName,
    OrderStatus::pending()
);

echo "Order created successfully!\n";
echo "Order ID: " . $order->getId()->getValue() . "\n";
echo "Customer: " . $order->getCustomerName()->getValue() . "\n";
echo "Status: " . $order->getStatus()->getValue() . "\n";
echo "Item Count: " . $order->getItemCount() . "\n";
echo "Total Price: " . $order->totalPrice()->format() . "\n\n";

// Display all items
echo "Order Items:\n";
foreach ($order->getItems() as $item) {
    echo "- " . $item->getProductName() . " (SKU: " . $item->getProductSku() . ")\n";
    echo "  Quantity: " . $item->getQuantity() . "\n";
    echo "  Unit Price: " . $item->getUnitPrice()->format() . "\n";
    echo "  Total: " . $item->getTotalPrice()->format() . "\n";
    if ($item->getDescription()) {
        echo "  Description: " . $item->getDescription() . "\n";
    }
    echo "\n";
}

// Example: Adding a new item
echo "=== Adding New Item ===\n";
$newItem = new OrderItem(
    'Headphones',
    'HEA-001',
    1,
    new MoneyDTO(149.99),
    'Noise-cancelling wireless headphones'
);

$order->addItem($newItem);
echo "Added headphones. New total: " . $order->totalPrice()->format() . "\n";
echo "New item count: " . $order->getItemCount() . "\n\n";

// Example: Finding and updating an item
echo "=== Finding and Updating Item ===\n";
$mouseItem = $order->findItem($item2->getId());
if ($mouseItem) {
    echo "Found mouse item. Current quantity: " . $mouseItem->getQuantity() . "\n";
    $mouseItem->updateQuantity(3);
    echo "Updated quantity to 3. New total: " . $order->totalPrice()->format() . "\n\n";
}

// Example: Order status transition
echo "=== Status Transition ===\n";
echo "Current status: " . $order->getStatus()->getValue() . "\n";
$order->updateStatus(OrderStatus::confirmed());
echo "Status updated to: " . $order->getStatus()->getValue() . "\n\n";

// Display final order summary
echo "=== Final Order Summary ===\n";
$orderArray = $order->toArray();
echo "Order ID: " . $orderArray['id'] . "\n";
echo "Customer: " . $orderArray['customer_name'] . "\n";
echo "Status: " . $orderArray['status'] . "\n";
echo "Total Items: " . count($orderArray['items']) . "\n";
echo "Total Price: " . $orderArray['total_price']['formatted'] . "\n";
