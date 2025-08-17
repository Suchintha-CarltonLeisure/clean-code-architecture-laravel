<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\ValueObjects\CustomerName;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Application\DTOs\MoneyDTO;
use App\Infrastructure\Events\InMemoryEventDispatcher;
use App\Domain\Customer\Events\CustomerOrderCreatedHandler;
use App\Domain\Inventory\Events\InventoryOrderCreatedHandler;
use App\Domain\Notification\Events\NotificationOrderStatusChangedHandler;
use Psr\Log\NullLogger;

echo "=== Domain Events Example ===\n\n";
echo "This example demonstrates cross-aggregate communication using Domain Events\n";
echo "in Clean Code Architecture with DDD principles.\n\n";

// Setup event system
$logger = new NullLogger();
$eventDispatcher = new InMemoryEventDispatcher($logger);

// Register event handlers
$customerHandler = new CustomerOrderCreatedHandler($logger);
$inventoryHandler = new InventoryOrderCreatedHandler($logger);
$notificationHandler = new NotificationOrderStatusChangedHandler($logger);

$eventDispatcher->subscribe('order.created', $customerHandler);
$eventDispatcher->subscribe('order.created', $inventoryHandler);
$eventDispatcher->subscribe('order.status_changed', $notificationHandler);

echo "✅ Event handlers registered:\n";
echo "  - CustomerOrderCreatedHandler (order.created)\n";
echo "  - InventoryOrderCreatedHandler (order.created)\n";
echo "  - NotificationOrderStatusChangedHandler (order.status_changed)\n\n";

// Create order items
$items = [
    new OrderItem(
        'MacBook Pro 16"',
        'MBP-16-001',
        1,
        new MoneyDTO(2499.99),
        'Apple MacBook Pro 16-inch with M3 Pro chip'
    ),
    new OrderItem(
        'USB-C Cable',
        'USB-C-001',
        3,
        new MoneyDTO(29.99),
        'High-quality USB-C charging cable'
    )
];

echo "=== Step 1: Creating Order (triggers OrderCreated event) ===\n";

// Create order - this will trigger OrderCreated domain event
$order = new Order(
    $items,
    CustomerName::fromString('Alice Johnson')
);

echo "Order created:\n";
echo "  - Customer: " . $order->getCustomerName()->getValue() . "\n";
echo "  - Items: " . count($order->getItems()) . "\n";
echo "  - Total: " . $order->totalPrice()->format() . "\n";
echo "  - Status: " . $order->getStatus()->getValue() . "\n\n";

// Simulate repository save (which would dispatch events)
echo "📡 Dispatching OrderCreated event...\n";
foreach ($order->getUncommittedEvents() as $event) {
    $eventDispatcher->dispatch($event);
    echo "  ✅ Event dispatched: " . $event->getEventName() . "\n";
    echo "     - Aggregate ID: " . $event->getAggregateId() . "\n";
    echo "     - Occurred On: " . $event->getOccurredOn()->format('Y-m-d H:i:s') . "\n";
}
$order->markEventsAsCommitted();

echo "\n=== Cross-Aggregate Effects ===\n";
echo "The OrderCreated event triggered the following side effects:\n";
echo "  🏪 Customer aggregate: Order statistics updated\n";
echo "  📦 Inventory aggregate: Stock reserved for order items\n";
echo "  📧 Notification system: Welcome email prepared\n\n";

echo "=== Step 2: Updating Order Status (triggers OrderStatusChanged event) ===\n";

// Update order status - this will trigger OrderStatusChanged domain event
$order->updateStatus(OrderStatus::confirmed());

echo "Order status updated:\n";
echo "  - Previous Status: pending\n";
echo "  - New Status: " . $order->getStatus()->getValue() . "\n\n";

// Dispatch status change events
echo "📡 Dispatching OrderStatusChanged event...\n";
foreach ($order->getUncommittedEvents() as $event) {
    $eventDispatcher->dispatch($event);
    echo "  ✅ Event dispatched: " . $event->getEventName() . "\n";
    echo "     - Aggregate ID: " . $event->getAggregateId() . "\n";
    echo "     - Status Change: " . $event->getEventData()['previous_status'] . " → " . $event->getEventData()['new_status'] . "\n";
}
$order->markEventsAsCommitted();

echo "\n=== Cross-Aggregate Effects ===\n";
echo "The OrderStatusChanged event triggered the following side effects:\n";
echo "  📧 Notification system: Order confirmation email sent\n";
echo "  📱 Push notification: Status update sent to mobile app\n";
echo "  📊 Analytics: Order confirmation metrics updated\n\n";

echo "=== Step 3: Further Status Changes ===\n";

// Ship the order
$order->updateStatus(OrderStatus::shipped());
foreach ($order->getUncommittedEvents() as $event) {
    $eventDispatcher->dispatch($event);
}
$order->markEventsAsCommitted();

echo "Order shipped - notification sent to customer\n";

// Deliver the order
$order->updateStatus(OrderStatus::delivered());
foreach ($order->getUncommittedEvents() as $event) {
    $eventDispatcher->dispatch($event);
}
$order->markEventsAsCommitted();

echo "Order delivered - delivery confirmation sent\n\n";

echo "=== Domain Events Benefits Demonstrated ===\n";
echo "✅ Loose Coupling: Aggregates don't directly depend on each other\n";
echo "✅ Single Responsibility: Each handler has one specific concern\n";
echo "✅ Extensibility: Easy to add new handlers without changing existing code\n";
echo "✅ Testability: Each handler can be tested in isolation\n";
echo "✅ Eventual Consistency: Side effects happen after main operation\n";
echo "✅ Audit Trail: All domain events can be logged for compliance\n\n";

echo "=== Real-World Applications ===\n";
echo "In production, these events could trigger:\n";
echo "  • Email/SMS notifications\n";
echo "  • Inventory management\n";
echo "  • Customer loyalty point updates\n";
echo "  • Analytics and reporting\n";
echo "  • Third-party system integrations\n";
echo "  • Audit logging\n";
echo "  • Event sourcing for complete history\n\n";

echo "=== Next Steps ===\n";
echo "Consider implementing:\n";
echo "  1. Event Store for persistence\n";
echo "  2. Async event processing with queues\n";
echo "  3. Event versioning for schema evolution\n";
echo "  4. Saga pattern for complex workflows\n";
echo "  5. Event replay for debugging and testing\n";
