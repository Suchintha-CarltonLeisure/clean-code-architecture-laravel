# Order Creation Process

## Overview

This document explains the complete Order creation process using Clean Architecture principles, Command Query Separation (CQS), and Domain-Driven Design (DDD).

## Architecture Flow

```
HTTP Request → Controller → Command → Handler → Domain → Repository → Response
```

## Step-by-Step Process

### 1. **Domain Layer - Order Items Creation**

**Location**: `app/Domain/Order/Entities/OrderItem.php`

```php
// Create individual order items
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
```

**Business Rules Enforced**:

-   Product name and SKU cannot be empty
-   Quantity must be greater than zero
-   Unit price cannot be negative
-   All strings are trimmed for consistency

### 2. **Application Layer - Command Creation**

**Location**: `app/Application/Commands/CreateOrder/CreateOrderCommand.php`

```php
$createOrderCommand = new CreateOrderCommand(
    [$laptopItem, $mouseItem, $keyboardItem],
    CustomerName::fromString('Alice Johnson')
);
```

**Command Structure**:

-   `items`: Array of OrderItem entities
-   `customerName`: CustomerName value object

### 3. **Application Layer - Command Handler**

**Location**: `app/Application/Commands/CreateOrder/CreateOrderHandler.php`

```php
final class CreateOrderHandler
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function handle(CreateOrderCommand $command): CreateOrderResponse
    {
        $order = new Order($command->items, $command->customerName);
        $saved = $this->orders->save($order);

        return new CreateOrderResponse($saved->getId()->getValue(), $saved->totalPrice());
    }
}
```

**Handler Responsibilities**:

-   Creates Order aggregate from command data
-   Saves order via repository
-   Returns response with order ID and total price

### 4. **Domain Layer - Order Aggregate**

**Location**: `app/Domain/Order/Entities/Order.php`

```php
class Order
{
    public function __construct(
        array $items,
        CustomerName $customerName,
        ?OrderStatus $status = null,
        ?OrderId $id = null
    ) {
        if (empty($items)) {
            throw new OrderModificationException("Order must contain at least one item.");
        }

        // Validate that all items are OrderItem instances
        foreach ($items as $item) {
            if (!$item instanceof OrderItem) {
                throw new OrderModificationException("All items must be OrderItem instances.");
            }
        }

        $this->items = $items;
        $this->customerName = $customerName;
        $this->status = $status ?? OrderStatus::pending();
        $this->id = $id ?? OrderId::generate();
    }
}
```

**Business Rules Enforced**:

-   Order must contain at least one item
-   All items must be valid OrderItem instances
-   Default status is 'pending'
-   OrderId is auto-generated if not provided

### 5. **Infrastructure Layer - Repository**

**Location**: `app/Infrastructure/Repositories/EloquentOrderRepository.php`

```php
interface OrderRepositoryInterface
{
    public function save(Order $order): Order;
    public function findById(OrderId $id): ?Order;
    public function deleteById(OrderId $id): bool;
    public function list(int $perPage = 15, int $page = 1): array;
    public function findByTotalPriceRange(MoneyDTO $minPrice, MoneyDTO $maxPrice): array;
}
```

**Repository Responsibilities**:

-   Persists Order aggregate to database
-   Retrieves orders by various criteria
-   Manages order lifecycle

### 6. **Application Layer - Response**

**Location**: `app/Application/Commands/CreateOrder/CreateOrderResponse.php`

```php
final class CreateOrderResponse
{
    public function __construct(
        public ?int $orderId,
        public readonly MoneyDTO $totalPrice
    ) {}

    public function getMessage(): string
    {
        return 'Order created successfully';
    }
}
```

## Complete Example

```php
// 1. Create order items
$items = [
    new OrderItem('Laptop', 'LAP-001', 1, new MoneyDTO(999.99)),
    new OrderItem('Mouse', 'MOU-001', 2, new MoneyDTO(29.99))
];

// 2. Create command
$command = new CreateOrderCommand(
    $items,
    CustomerName::fromString('John Doe')
);

// 3. Execute command
$handler = new CreateOrderHandler($orderRepository);
$response = $handler->handle($command);

// 4. Process response
echo "Order created with ID: " . $response->orderId;
echo "Total: " . $response->totalPrice->format();
```

## Business Rules Validation

### **Order Creation Rules**:

-   ✅ Must have at least one item
-   ✅ All items must be valid OrderItem instances
-   ✅ Customer name must be valid
-   ✅ Order status defaults to 'pending'

### **Order Item Rules**:

-   ✅ Product name cannot be empty
-   ✅ Product SKU cannot be empty
-   ✅ Quantity must be greater than zero
-   ✅ Unit price cannot be negative

### **Status Transition Rules**:

-   ✅ `pending` → `confirmed` or `cancelled`
-   ✅ `confirmed` → `shipped` or `cancelled`
-   ✅ `shipped` → `delivered`
-   ✅ `delivered` and `cancelled` are terminal states

## Error Handling

```php
try {
    $response = $handler->handle($command);
    // Success
} catch (OrderModificationException $e) {
    // Business rule violation
    echo "Business Error: " . $e->getMessage();
} catch (\Exception $e) {
    // System error
    echo "System Error: " . $e->getMessage();
}
```

## Benefits of This Architecture

1. **Separation of Concerns**: Each layer has a specific responsibility
2. **Testability**: Easy to unit test each component
3. **Business Rule Enforcement**: Rules are centralized in the domain layer
4. **Type Safety**: Strong typing with PHP 8.2 features
5. **Maintainability**: Clear structure and dependencies
6. **Scalability**: Easy to add new commands and handlers

## Testing the Process

Run the complete example:

```bash
php examples/OrderCreationProcessExample.php
```

This will demonstrate:

-   Order item creation
-   Command execution
-   Business rule validation
-   Repository persistence
-   Response handling
-   Business rule testing

## Next Steps

Consider implementing:

1. **Domain Events** for order creation
2. **Validation Middleware** for HTTP requests
3. **Command Bus** for complex workflows
4. **Event Sourcing** for audit trails
5. **CQRS** for read/write separation
