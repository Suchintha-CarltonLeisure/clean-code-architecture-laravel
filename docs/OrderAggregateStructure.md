# Order Aggregate Structure

## Overview

The Order aggregate root has been enhanced to include `OrderItem` entities, following Domain-Driven Design principles and Clean Architecture patterns.

## Architecture

```
Order (Aggregate Root)
├── OrderId (Value Object)
├── CustomerName (Value Object)
├── OrderStatus (Value Object)
├── OrderItem[] (Entity Collection)
│   ├── OrderItemId (Value Object)
│   ├── productName (string)
│   ├── productSku (string)
│   ├── quantity (int)
│   ├── unitPrice (MoneyDTO)
│   └── description (string|null)
└── Business Methods
    ├── addItem()
    ├── removeItem()
    ├── findItem()
    ├── updateItems()
    ├── totalPrice()
    └── updateStatus()
```

## Key Components

### 1. OrderItem Entity

**Location**: `app/Domain/Order/Entities/OrderItem.php`

**Properties**:

-   `OrderItemId`: Unique identifier for each item
-   `productName`: Human-readable product name
-   `productSku`: Stock keeping unit (unique product code)
-   `quantity`: Number of items ordered
-   `unitPrice`: Price per unit (MoneyDTO)
-   `description`: Optional product description

**Business Rules**:

-   Product name and SKU cannot be empty
-   Quantity must be greater than zero
-   Unit price cannot be negative
-   All strings are trimmed for consistency

**Methods**:

-   `getTotalPrice()`: Calculates item total (quantity × unit price)
-   `updateQuantity()`: Updates item quantity with validation
-   `updateUnitPrice()`: Updates unit price
-   `updateDescription()`: Updates product description
-   `equals()`: Compares items by ID

### 2. OrderItemId Value Object

**Location**: `app/Domain/Order/ValueObjects/OrderItemId.php`

**Features**:

-   Uses UUID v4 for uniqueness
-   Immutable design
-   Factory methods: `generate()` and `fromString()`
-   Validation for UUID format

### 3. Enhanced Order Aggregate

**New Methods**:

-   `addItem(OrderItem $item)`: Adds new item to order
-   `removeItem(OrderItemId $itemId)`: Removes specific item
-   `findItem(OrderItemId $itemId)`: Finds item by ID
-   `getItemCount()`: Returns total number of items
-   Enhanced `totalPrice()`: Calculates sum of all item totals

**Business Rules**:

-   Order must contain at least one item
-   Cannot remove all items from an order
-   All items must be valid OrderItem instances
-   Total price is calculated from item totals

## Usage Example

```php
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

// Create order
$order = new Order(
    [$item1, $item2],
    CustomerName::fromString('John Doe'),
    OrderStatus::pending()
);

// Add new item
$newItem = new OrderItem('Headphones', 'HEA-001', 1, new MoneyDTO(149.99));
$order->addItem($newItem);

// Get total price
$total = $order->totalPrice(); // USD 1,329.94
```

## Benefits

1. **Encapsulation**: Order items are managed within the Order aggregate
2. **Business Rules**: Validation and business logic are centralized
3. **Immutability**: Value objects ensure data integrity
4. **Testability**: Easy to test business logic in isolation
5. **Maintainability**: Clear separation of concerns
6. **Type Safety**: Strong typing with PHP 8.2 features

## DDD Principles Applied

-   **Aggregate Root**: Order manages the lifecycle of OrderItems
-   **Value Objects**: OrderItemId, MoneyDTO ensure immutability
-   **Entities**: OrderItem has identity and lifecycle
-   **Invariants**: Business rules are enforced at the aggregate level
-   **Repository Pattern**: OrderRepositoryInterface abstracts data access

## Next Steps

Consider implementing:

1. **Domain Events** for order state changes
2. **Specification Pattern** for complex querying
3. **Unit of Work** for transaction management
4. **Event Sourcing** for order history tracking
