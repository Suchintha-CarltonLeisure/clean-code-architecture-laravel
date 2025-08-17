<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\Commands\CreateOrder\CreateOrderCommand;
use App\Application\Commands\CreateOrder\CreateOrderHandler;
use App\Application\Commands\CreateOrder\CreateOrderResponse;
use App\Application\DTOs\MoneyDTO;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\ValueObjects\CustomerName;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Infrastructure\Repositories\EloquentOrderRepository;

// Example: Complete Order Creation Process
echo "=== Order Creation Process Example ===\n\n";

// Step 1: Create Order Items
echo "Step 1: Creating Order Items\n";
echo "-----------------------------\n";

$laptopItem = new OrderItem(
    'MacBook Pro 16"',
    'MBP-16-001',
    1,
    new MoneyDTO(2499.99),
    'Apple MacBook Pro 16-inch with M3 Pro chip'
);

$mouseItem = new OrderItem(
    'Magic Mouse 2',
    'MM-002',
    1,
    new MoneyDTO(79.99),
    'Wireless Magic Mouse 2 with rechargeable battery'
);

$keyboardItem = new OrderItem(
    'Magic Keyboard',
    'MK-003',
    1,
    new MoneyDTO(99.99),
    'Wireless Magic Keyboard with numeric keypad'
);

echo "✓ Created laptop item: " . $laptopItem->getProductName() . " - " . $laptopItem->getTotalPrice()->format() . "\n";
echo "✓ Created mouse item: " . $mouseItem->getProductName() . " - " . $mouseItem->getTotalPrice()->format() . "\n";
echo "✓ Created keyboard item: " . $keyboardItem->getProductName() . " - " . $keyboardItem->getTotalPrice()->format() . "\n\n";

// Step 2: Prepare Create Order Command
echo "Step 2: Preparing Create Order Command\n";
echo "-------------------------------------\n";

$customerName = CustomerName::fromString('Alice Johnson');
$items = [$laptopItem, $mouseItem, $keyboardItem];

// Calculate total for verification
$totalAmount = 0;
foreach ($items as $item) {
    $totalAmount += $item->getTotalPrice()->getAmount();
}

echo "Customer: " . $customerName->getValue() . "\n";
echo "Number of items: " . count($items) . "\n";
echo "Expected total: " . number_format($totalAmount, 2) . "\n\n";

// Step 3: Create and Execute Command
echo "Step 3: Creating and Executing Command\n";
echo "-------------------------------------\n";

// Create the command
$createOrderCommand = new CreateOrderCommand(
    $items,
    $customerName
);

echo "✓ Created CreateOrderCommand\n";
echo "Command data:\n";
echo "- Customer: " . $createOrderCommand->customerName->getValue() . "\n";
echo "- Items count: " . count($createOrderCommand->items) . "\n\n";

// Step 4: Execute Command with Handler
echo "Step 4: Executing Command with Handler\n";
echo "--------------------------------------\n";

// In a real application, this would be injected via dependency injection
// For this example, we'll create a mock repository
class MockOrderRepository implements \App\Domain\Order\Repositories\OrderRepositoryInterface
{
    private array $orders = [];
    private int $nextId = 1;

    public function save(Order $order): Order
    {
        // Simulate saving to database
        $this->orders[] = $order;
        echo "✓ Order saved to repository (ID: " . $order->getId()->getValue() . ")\n";
        return $order;
    }

    public function findById(\App\Domain\Order\ValueObjects\OrderId $id): ?Order
    {
        foreach ($this->orders as $order) {
            if ($order->getId()->getValue() === $id->getValue()) {
                return $order;
            }
        }
        return null;
    }

    public function deleteById(\App\Domain\Order\ValueObjects\OrderId $id): bool
    {
        $this->orders = array_filter($this->orders, function (Order $order) use ($id) {
            return $order->getId()->getValue() !== $id->getValue();
        });
        return true;
    }

    public function list(int $perPage = 15, int $page = 1): array
    {
        return array_slice($this->orders, ($page - 1) * $perPage, $perPage);
    }

    public function findByTotalPriceRange(\App\Application\DTOs\MoneyDTO $minPrice, \App\Application\DTOs\MoneyDTO $maxPrice): array
    {
        return array_filter($this->orders, function (Order $order) use ($minPrice, $maxPrice) {
            $total = $order->totalPrice();
            return $total->getAmount() >= $minPrice->getAmount() && $total->getAmount() <= $maxPrice->getAmount();
        });
    }

    public function findAll(): array
    {
        return $this->orders;
    }
}

// Create handler with mock repository
$orderRepository = new MockOrderRepository();
$createOrderHandler = new CreateOrderHandler($orderRepository);

echo "✓ Created CreateOrderHandler with mock repository\n\n";

// Execute the command
try {
    $response = $createOrderHandler->handle($createOrderCommand);
    echo "✓ Command executed successfully!\n";
    echo "Response: " . $response->toArray()['message'] . "\n";
    echo "Order ID: " . $response->orderId . "\n\n";
} catch (\Exception $e) {
    echo "✗ Error executing command: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 5: Verify Created Order
echo "Step 5: Verifying Created Order\n";
echo "-------------------------------\n";

// Retrieve the created order from repository
$orderId = \App\Domain\Order\ValueObjects\OrderId::fromInt($response->orderId);
$createdOrder = $orderRepository->findById($orderId);

if ($createdOrder) {
    echo "✓ Order retrieved from repository\n";
    echo "Order details:\n";
    echo "- ID: " . $createdOrder->getId()->getValue() . "\n";
    echo "- Customer: " . $createdOrder->getCustomerName()->getValue() . "\n";
    echo "- Status: " . $createdOrder->getStatus()->getValue() . "\n";
    echo "- Item count: " . $createdOrder->getItemCount() . "\n";
    echo "- Total price: " . $createdOrder->totalPrice()->format() . "\n\n";

    echo "Order items:\n";
    foreach ($createdOrder->getItems() as $index => $item) {
        echo ($index + 1) . ". " . $item->getProductName() . "\n";
        echo "   SKU: " . $item->getProductSku() . "\n";
        echo "   Quantity: " . $item->getQuantity() . "\n";
        echo "   Unit Price: " . $item->getUnitPrice()->format() . "\n";
        echo "   Total: " . $item->getTotalPrice()->format() . "\n";
        if ($item->getDescription()) {
            echo "   Description: " . $item->getDescription() . "\n";
        }
        echo "\n";
    }
} else {
    echo "✗ Failed to retrieve created order from repository\n";
}

// Step 6: Demonstrate Business Rules
echo "Step 6: Demonstrating Business Rules\n";
echo "------------------------------------\n";

echo "Testing business rule: Cannot create order with empty items\n";
try {
    $emptyOrderCommand = new CreateOrderCommand(
        [],
        CustomerName::fromString('Test Customer')
    );
    $createOrderHandler->handle($emptyOrderCommand);
    echo "✗ Should have failed with empty items\n";
} catch (\Exception $e) {
    echo "✓ Correctly prevented order creation with empty items: " . $e->getMessage() . "\n";
}

echo "\nTesting business rule: Order status transitions\n";
echo "Current status: " . $createdOrder->getStatus()->getValue() . "\n";
echo "Can transition to confirmed: " . ($createdOrder->getStatus()->canTransitionTo(OrderStatus::confirmed()) ? 'Yes' : 'No') . "\n";
echo "Can transition to shipped: " . ($createdOrder->getStatus()->canTransitionTo(OrderStatus::shipped()) ? 'Yes' : 'No') . "\n";
echo "Can transition to cancelled: " . ($createdOrder->getStatus()->canTransitionTo(OrderStatus::cancelled()) ? 'Yes' : 'No') . "\n\n";

// Step 7: Process Summary
echo "Step 7: Process Summary\n";
echo "----------------------\n";

echo "✓ Order creation process completed successfully!\n";
echo "✓ All business rules enforced\n";
echo "✓ Clean Architecture layers properly separated\n";
echo "✓ Domain logic encapsulated in Order aggregate\n";
echo "✓ Application layer handles command execution\n";
echo "✓ Infrastructure layer manages persistence\n\n";

echo "Total orders in repository: " . count($orderRepository->findAll()) . "\n";
echo "Final order total: " . $createdOrder->totalPrice()->format() . "\n";
