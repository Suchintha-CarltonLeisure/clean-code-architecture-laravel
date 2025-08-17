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

// Example: API Order Creation with Correct Payload Structure
echo "=== API Order Creation Example ===\n\n";

// Step 1: Simulate HTTP Request Payload
echo "Step 1: HTTP Request Payload\n";
echo "-----------------------------\n";

$httpPayload = [
    'customer_name' => 'Alice Johnson',
    'items' => [
        [
            'product_name' => 'MacBook Pro 16"',
            'product_sku' => 'MBP-16-001',
            'quantity' => 1,
            'unit_price' => 2499.99,
            'description' => 'Apple MacBook Pro 16-inch with M3 Pro chip'
        ],
        [
            'product_name' => 'Magic Mouse 2',
            'product_sku' => 'MM-002',
            'quantity' => 1,
            'unit_price' => 79.99,
            'description' => 'Wireless Magic Mouse 2 with rechargeable battery'
        ],
        [
            'product_name' => 'Magic Keyboard',
            'product_sku' => 'MK-003',
            'quantity' => 1,
            'unit_price' => 99.99,
            'description' => 'Wireless Magic Keyboard with numeric keypad'
        ]
    ]
];

echo "HTTP POST /api/orders\n";
echo "Content-Type: application/json\n\n";
echo "Payload:\n";
echo json_encode($httpPayload, JSON_PRETTY_PRINT) . "\n\n";

// Step 2: Validation (Simulating CreateOrderRequest)
echo "Step 2: Request Validation\n";
echo "--------------------------\n";

// Simulate validation rules
$validationRules = [
    'customer_name' => 'required|string|min:2|max:100|regex:/^[a-zA-Z\s\'-]+$/',
    'items' => 'required|array|min:1',
    'items.*.product_name' => 'required|string|min:1|max:255',
    'items.*.product_sku' => 'required|string|min:1|max:100',
    'items.*.quantity' => 'required|integer|min:1',
    'items.*.unit_price' => 'required|numeric|min:0',
    'items.*.description' => 'nullable|string|max:500',
];

echo "Validation Rules:\n";
foreach ($validationRules as $field => $rule) {
    echo "- {$field}: {$rule}\n";
}

// Validate payload
$errors = [];
if (empty($httpPayload['customer_name'])) {
    $errors[] = 'Customer name is required';
}
if (empty($httpPayload['items']) || !is_array($httpPayload['items'])) {
    $errors[] = 'Items array is required and must contain at least one item';
}

foreach ($httpPayload['items'] as $index => $item) {
    if (empty($item['product_name'])) {
        $errors[] = "Item {$index}: Product name is required";
    }
    if (empty($item['product_sku'])) {
        $errors[] = "Item {$index}: Product SKU is required";
    }
    if (!isset($item['quantity']) || $item['quantity'] < 1) {
        $errors[] = "Item {$index}: Quantity must be at least 1";
    }
    if (!isset($item['unit_price']) || $item['unit_price'] < 0) {
        $errors[] = "Item {$index}: Unit price must be non-negative";
    }
}

if (!empty($errors)) {
    echo "\n❌ Validation Errors:\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}

echo "\n✅ Validation passed successfully!\n\n";

// Step 3: Create Command from Request Data
echo "Step 3: Creating Command from Request Data\n";
echo "-----------------------------------------\n";

$customerName = CustomerName::fromString($httpPayload['customer_name']);
$createOrderCommand = new CreateOrderCommand(
    $httpPayload['items'],
    $customerName
);

echo "✓ CreateOrderCommand created\n";
echo "Customer: " . $createOrderCommand->customerName->getValue() . "\n";
echo "Items count: " . count($createOrderCommand->items) . "\n\n";

// Step 4: Execute Command Handler
echo "Step 4: Executing Command Handler\n";
echo "--------------------------------\n";

// Mock repository for demonstration
class MockOrderRepository implements \App\Domain\Order\Repositories\OrderRepositoryInterface
{
    private array $orders = [];

    public function save(Order $order): Order
    {
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
}

$orderRepository = new MockOrderRepository();
$createOrderHandler = new CreateOrderHandler($orderRepository);

echo "✓ CreateOrderHandler created with mock repository\n\n";

// Execute the command
try {
    $response = $createOrderHandler->handle($createOrderCommand);
    echo "✓ Command executed successfully!\n";
    echo "Response: " . $response->getMessage() . "\n";
    echo "Order ID: " . $response->orderId . "\n";
    echo "Total Price: " . $response->totalPrice->format() . "\n\n";
} catch (\Exception $e) {
    echo "❌ Error executing command: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 5: Verify Created Order
echo "Step 5: Verifying Created Order\n";
echo "-------------------------------\n";

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
    echo "❌ Failed to retrieve created order from repository\n";
}

// Step 6: API Response Format
echo "Step 6: API Response Format\n";
echo "---------------------------\n";

$apiResponse = [
    'status' => 'success',
    'status_code' => 201,
    'data' => $response->toArray(),
    'message' => 'Order created successfully'
];

echo "HTTP Response (201 Created):\n";
echo "Content-Type: application/json\n\n";
echo json_encode($apiResponse, JSON_PRETTY_PRINT) . "\n\n";

// Step 7: Summary
echo "Step 7: Summary\n";
echo "---------------\n";

echo "✅ Complete API Order Creation Process:\n";
echo "1. HTTP POST request with JSON payload\n";
echo "2. Request validation using CreateOrderRequest\n";
echo "3. CreateOrderCommand creation\n";
echo "4. Command execution via CreateOrderHandler\n";
echo "5. Domain Order aggregate creation\n";
echo "6. Repository persistence\n";
echo "7. API response with order details\n\n";

echo "📋 Required Payload Fields:\n";
echo "- customer_name: string (2-100 chars, letters/spaces/hyphens/apostrophes only)\n";
echo "- items: array of objects with:\n";
echo "  - product_name: string (1-255 chars)\n";
echo "  - product_sku: string (1-100 chars)\n";
echo "  - quantity: integer (min: 1)\n";
echo "  - unit_price: numeric (min: 0)\n";
echo "  - description: string (optional, max 500 chars)\n\n";

echo "🔗 API Endpoint: POST /api/orders\n";
echo "📝 Content-Type: application/json\n";
echo "✅ Success Response: 201 Created\n";
echo "❌ Error Response: 422 Unprocessable Entity (validation errors)\n";
