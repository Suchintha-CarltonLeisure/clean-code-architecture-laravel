<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\ValueObjects\CustomerName;
use App\Application\DTOs\MoneyDTO;
use App\Infrastructure\Presenters\Api\OrderPresenter;
use App\Infrastructure\Presenters\Api\ResponsePresenter;

echo "=== Infrastructure Presenters Example ===\n\n";
echo "This demonstrates the proper Clean Architecture structure where\n";
echo "Presenters belong in the Infrastructure layer as outer-layer implementations.\n\n";

// Create sample order
$items = [
    new OrderItem(
        'MacBook Pro 16"',
        'MBP-16-001',
        1,
        new MoneyDTO(2499.99),
        'Apple MacBook Pro 16-inch with M3 Pro chip'
    ),
    new OrderItem(
        'Magic Mouse 2',
        'MM-002',
        1,
        new MoneyDTO(79.99),
        'Wireless Magic Mouse 2'
    )
];

$order = new Order(
    $items,
    CustomerName::fromString('Alice Johnson')
);

echo "=== Architecture Structure ===\n";
echo "├── Infrastructure/\n";
echo "│   ├── Repositories/\n";
echo "│   │   └── EloquentOrderRepository.php\n";
echo "│   └── Presenters/                     # ← Presentation Layer\n";
echo "│       └── Api/\n";
echo "│           ├── OrderPresenter.php      # ← Domain-to-API formatting\n";
echo "│           └── ResponsePresenter.php   # ← Standardized responses\n\n";

// Initialize presenters
$orderPresenter = new OrderPresenter();
$responsePresenter = new ResponsePresenter();

echo "=== OrderPresenter Output ===\n";
$presentedOrder = $orderPresenter->present($order);
echo json_encode($presentedOrder, JSON_PRETTY_PRINT) . "\n\n";

echo "=== ResponsePresenter - Success Response ===\n";
$successResponse = $responsePresenter->presentSuccess($presentedOrder, 'Order retrieved successfully');
echo json_encode($successResponse, JSON_PRETTY_PRINT) . "\n\n";

echo "=== ResponsePresenter - Created Response ===\n";
$createdResponse = $responsePresenter->presentCreated($presentedOrder, 'Order created successfully');
echo json_encode($createdResponse, JSON_PRETTY_PRINT) . "\n\n";

echo "=== ResponsePresenter - Error Response ===\n";
$errorResponse = $responsePresenter->presentError('Order not found', null, 404);
echo json_encode($errorResponse, JSON_PRETTY_PRINT) . "\n\n";

echo "=== Clean Architecture Benefits ===\n";
echo "✅ Separation of Concerns: Presenters handle only output formatting\n";
echo "✅ Infrastructure Layer: Outer layer implementation as per Clean Architecture\n";
echo "✅ Domain Independence: Domain objects don't know about presentation\n";
echo "✅ Testability: Presenters can be tested independently\n";
echo "✅ Consistency: Standardized API response format across all endpoints\n";
echo "✅ Flexibility: Easy to add new presentation formats (Web, Mobile, etc.)\n\n";

echo "=== Usage in Controllers ===\n";
echo "Controllers now use Infrastructure Presenters:\n";
echo "• OrderController injects OrderPresenter and ResponsePresenter\n";
echo "• Domain objects are formatted using OrderPresenter\n";
echo "• HTTP responses are standardized using ResponsePresenter\n";
echo "• Clean separation between business logic and presentation\n\n";

echo "=== API Response Structure ===\n";
echo "All API responses follow a consistent format:\n";
echo "{\n";
echo "  \"success\": true|false,\n";
echo "  \"message\": \"descriptive message\",\n";
echo "  \"data\": {...},\n";
echo "  \"timestamp\": \"ISO 8601 format\"\n";
echo "}\n";
