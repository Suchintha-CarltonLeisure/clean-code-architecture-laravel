<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Order\ValueObjects\CustomerName;

echo "=== Value Objects Example ===\n\n";

// Example 1: OrderId Value Object
echo "1. OrderId Value Object:\n";
$orderId = OrderId::fromInt(123);
echo "Order ID: " . $orderId->getValue() . "\n";
echo "Is null: " . ($orderId->isNull() ? 'Yes' : 'No') . "\n";
echo "String representation: " . (string) $orderId . "\n\n";

// Example 2: OrderStatus Value Object
echo "2. OrderStatus Value Object:\n";
$status = OrderStatus::pending();
echo "Status: " . $status->getValue() . "\n";
echo "Is pending: " . ($status->isPending() ? 'Yes' : 'No') . "\n";

$confirmed = OrderStatus::confirmed();
echo "Can transition from pending to confirmed: " . ($status->canTransitionTo($confirmed) ? 'Yes' : 'No') . "\n";
echo "Can transition from pending to delivered: " . ($status->canTransitionTo(OrderStatus::delivered()) ? 'Yes' : 'No') . "\n\n";

// Example 3: CustomerName Value Object
echo "3. CustomerName Value Object:\n";
$customerName = CustomerName::fromString('John Michael Doe');
echo "Full name: " . $customerName->getValue() . "\n";
echo "First name: " . $customerName->getFirstName() . "\n";
echo "Last name: " . $customerName->getLastName() . "\n";
echo "Initials: " . $customerName->getInitials() . "\n\n";

// Example 4: Using value objects together
echo "4. Using Value Objects Together:\n";
$order = [
      'id' => OrderId::fromInt(456),
      'status' => OrderStatus::confirmed(),
      'customer' => CustomerName::fromString('Jane Smith')
];

echo "Order ID: " . $order['id']->getValue() . "\n";
echo "Order Status: " . $order['status']->getValue() . "\n";
echo "Customer: " . $order['customer']->getValue() . " (" . $order['customer']->getInitials() . ")\n\n";

// Example 5: Validation examples
echo "5. Validation Examples:\n";
try {
      $invalidOrderId = OrderId::fromInt(0);
} catch (InvalidArgumentException $e) {
      echo "OrderId validation: " . $e->getMessage() . "\n";
}

try {
      $invalidStatus = OrderStatus::fromString('invalid_status');
} catch (InvalidArgumentException $e) {
      echo "OrderStatus validation: " . $e->getMessage() . "\n";
}

try {
      $invalidName = CustomerName::fromString('John123');
} catch (InvalidArgumentException $e) {
      echo "CustomerName validation: " . $e->getMessage() . "\n";
}

echo "\n=== End of Examples ===\n";