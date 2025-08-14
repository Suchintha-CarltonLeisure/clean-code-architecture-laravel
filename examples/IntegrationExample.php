<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Domain\Order\Entities\Order;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\CustomerName;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Application\Commands\CreateOrder\CreateOrderCommand;
use App\Application\Commands\CreateOrder\CreateOrderHandler;
use App\Application\Queries\GetOrder\GetOrderQuery;
use App\Application\Queries\GetOrder\GetOrderHandler;
use Tests\Unit\InMemoryOrderRepository;

echo "=== Value Objects Integration Example ===\n\n";

// Create an in-memory repository for testing
$repository = new InMemoryOrderRepository();

// Example 1: Creating an Order with Value Objects
echo "1. Creating an Order with Value Objects:\n";
$customerName = CustomerName::fromString('John Michael Doe');
$items = [
      ['name' => 'Laptop', 'price' => 999.99],
      ['name' => 'Mouse', 'price' => 29.99]
];

$order = new Order($items, $customerName);
echo "Order created with ID: " . $order->getId()->getValue() . "\n";
echo "Customer: " . $order->getCustomerName()->getValue() . " (" . $order->getCustomerName()->getInitials() . ")\n";
echo "Status: " . $order->getStatus()->getValue() . "\n";
echo "Total Price: " . $order->totalPrice()->format() . "\n\n";

// Example 2: Using the Application Layer with Value Objects
echo "2. Using Application Layer with Value Objects:\n";
$createHandler = new CreateOrderHandler($repository);
$getHandler = new GetOrderHandler($repository);

// Create order through command
$createCommand = new CreateOrderCommand($items, $customerName);
$createResponse = $createHandler->handle($createCommand);
echo "Created order with ID: " . $createResponse->orderId . "\n";
echo "Total price: " . $createResponse->totalPrice->format() . "\n\n";

// Get order through query
$orderId = OrderId::fromInt($createResponse->orderId);
$getQuery = new GetOrderQuery($orderId);
$getResponse = $getHandler->handle($getQuery);

if ($getResponse) {
      echo "Retrieved order:\n";
      echo "- ID: " . $getResponse->order['id'] . "\n";
      echo "- Customer: " . $getResponse->order['customer_name'] . "\n";
      echo "- First Name: " . $getResponse->order['customer_first_name'] . "\n";
      echo "- Last Name: " . $getResponse->order['customer_last_name'] . "\n";
      echo "- Initials: " . $getResponse->order['customer_initials'] . "\n";
      echo "- Status: " . $getResponse->order['status'] . "\n";
      echo "- Total Price: " . $getResponse->order['total_price']['formatted'] . "\n\n";
}

// Example 3: Status Transitions with Value Objects
echo "3. Status Transitions with Value Objects:\n";
$order = $repository->findById($orderId);
if ($order) {
      echo "Current status: " . $order->getStatus()->getValue() . "\n";

      // Try to transition to confirmed
      if ($order->getStatus()->canTransitionTo(OrderStatus::confirmed())) {
            $order->updateStatus(OrderStatus::confirmed());
            echo "Status updated to: " . $order->getStatus()->getValue() . "\n";
      }

      // Try to transition to shipped
      if ($order->getStatus()->canTransitionTo(OrderStatus::shipped())) {
            $order->updateStatus(OrderStatus::shipped());
            echo "Status updated to: " . $order->getStatus()->getValue() . "\n";
      }

      // Try invalid transition
      try {
            $order->updateStatus(OrderStatus::pending());
      } catch (Exception $e) {
            echo "Invalid transition caught: " . $e->getMessage() . "\n";
      }
}

echo "\n=== End of Integration Example ===\n";