# Domain Events Workflow - Cross-Aggregate Communication

## Overview

Domain Events enable loose coupling between aggregates by allowing them to communicate indirectly through events rather than direct references. This maintains aggregate boundaries while enabling complex business workflows.

## Workflow Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           DOMAIN EVENTS WORKFLOW                                │
└─────────────────────────────────────────────────────────────────────────────────┘

1. BUSINESS OPERATION (Order Creation)
   ┌─────────────────┐
   │   HTTP Request  │
   │  POST /orders   │
   └─────────┬───────┘
             │
             ▼
   ┌─────────────────┐
   │  OrderController│
   │   (HTTP Layer)  │
   └─────────┬───────┘
             │
             ▼
   ┌─────────────────┐
   │ CreateOrderHandler│
   │ (Application)   │
   └─────────┬───────┘
             │
             ▼
   ┌─────────────────┐
   │ Order Aggregate │ ◄─── Business Logic
   │   (Domain)      │      • Validates items
   └─────────┬───────┘      • Calculates total
             │              • Records OrderCreated event
             │
2. EVENT RECORDING
             │
             ▼
   ┌─────────────────┐
   │  AggregateRoot  │
   │ recordEvent()   │ ◄─── Stores event in memory
   └─────────┬───────┘      (uncommitted events)
             │
             │
3. PERSISTENCE & EVENT DISPATCH
             │
             ▼
   ┌─────────────────┐
   │EloquentOrderRepo│
   │    save()       │ ◄─── 1. Saves to database
   └─────────┬───────┘      2. Dispatches events
             │              3. Marks events committed
             │
             ▼
   ┌─────────────────┐
   │ EventDispatcher │ ◄─── Distributes events to
   │   dispatch()    │      registered handlers
   └─────────┬───────┘
             │
             │
4. CROSS-AGGREGATE COMMUNICATION
             │
    ┌────────┼────────┐
    │        │        │
    ▼        ▼        ▼
┌─────────┐ ┌─────────┐ ┌─────────┐
│Customer │ │Inventory│ │Notification│
│Handler  │ │Handler  │ │ Handler │
└─────────┘ └─────────┘ └─────────┘
    │        │        │
    ▼        ▼        ▼
┌─────────┐ ┌─────────┐ ┌─────────┐
│Customer │ │Inventory│ │Notification│
│Aggregate│ │Aggregate│ │ Service │
└─────────┘ └─────────┘ └─────────┘

5. SIDE EFFECTS
• Update customer stats    • Reserve inventory    • Send welcome email
• Calculate loyalty points • Check stock levels   • Create notifications
• Update purchase history  • Trigger reorders     • Log communication
```

## Step-by-Step Flow

### Step 1: Business Operation Initiation
```php
// HTTP Request triggers business operation
POST /api/orders
{
  "items": [...],
  "customer_name": "Alice Johnson"
}
```

### Step 2: Domain Logic Execution
```php
// Order aggregate handles business logic
$order = new Order($items, $customerName);

// Inside Order constructor:
$this->recordEvent(new OrderCreated(
    $this->id,
    $this->customerName,
    $this->totalPrice(),
    count($this->items),
    new DateTimeImmutable()
));
```

### Step 3: Event Storage (In-Memory)
```php
// AggregateRoot stores events temporarily
abstract class AggregateRoot
{
    private array $domainEvents = [];
    
    protected function recordEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event; // ← Event stored here
    }
}
```

### Step 4: Persistence & Event Dispatch
```php
// Repository saves aggregate and dispatches events
public function save(Order $order): Order
{
    // 1. Save to database
    $elo->save();
    
    // 2. Dispatch all uncommitted events
    foreach ($order->getUncommittedEvents() as $event) {
        $this->eventDispatcher->dispatch($event); // ← Events dispatched here
    }
    
    // 3. Mark events as committed
    $order->markEventsAsCommitted();
    
    return $order;
}
```

### Step 5: Event Distribution
```php
// EventDispatcher finds and executes handlers
public function dispatch(DomainEvent $event): void
{
    foreach ($this->handlers as $handler) {
        if ($handler->canHandle($event->getEventName())) {
            $handler->handle($event); // ← Handler executed
        }
    }
}
```

### Step 6: Cross-Aggregate Effects
```php
// Each handler updates its respective aggregate
class CustomerOrderCreatedHandler
{
    public function handle(DomainEvent $event): void
    {
        // 1. Find Customer aggregate
        // 2. Update order count and total spent
        // 3. Check loyalty tier upgrades
        // 4. Save Customer aggregate
    }
}
```

## Event Flow Timeline

```
Time →  [T1]      [T2]      [T3]      [T4]      [T5]
        │         │         │         │         │
        │         │         │         │         │
Order   │ Create  │ Record  │ Save    │         │
        │ Order   │ Event   │ to DB   │         │
        │         │         │         │         │
        │         │         │ Dispatch│         │
        │         │         │ Events  │         │
        │         │         │         │         │
Customer│         │         │         │ Update  │ Save
        │         │         │         │ Stats   │ Customer
        │         │         │         │         │
Inventory│        │         │         │ Reserve │ Update
        │         │         │         │ Stock   │ Inventory
        │         │         │         │         │
Notify  │         │         │         │ Send    │ Log
        │         │         │         │ Email   │ History
```

## Key Characteristics

### 1. **Eventual Consistency**
- Main operation (order creation) completes first
- Side effects happen afterward
- System remains consistent over time

### 2. **Loose Coupling**
- Order doesn't know about Customer, Inventory, or Notification
- Communication happens through events only
- Easy to add/remove handlers

### 3. **Single Responsibility**
- Each handler has one specific concern
- Order aggregate focuses only on order logic
- Cross-cutting concerns are separated

### 4. **Transactional Boundaries**
- Each aggregate maintains its own transaction
- Events cross transaction boundaries
- Failure in one handler doesn't affect others

## Error Handling

```php
// EventDispatcher handles failures gracefully
foreach ($this->handlers as $handler) {
    try {
        $handler->handle($event);
    } catch (\Exception $e) {
        $this->logger->error('Event handler failed', [
            'handler' => get_class($handler),
            'error' => $e->getMessage()
        ]);
        // Continue with other handlers
    }
}
```

## Benefits Demonstrated

1. **Maintainability**: Easy to modify one aggregate without affecting others
2. **Testability**: Each component can be tested in isolation
3. **Scalability**: Handlers can be moved to background queues
4. **Extensibility**: New handlers can be added without code changes
5. **Auditability**: All events can be logged for compliance

## Real-World Scenarios

### Order Status Change Flow
```
Order.updateStatus() → OrderStatusChanged Event → NotificationHandler
                                                ↓
                                           Send Email/SMS
```

### Customer Loyalty Update Flow
```
Order.create() → OrderCreated Event → CustomerHandler
                                    ↓
                               Update Loyalty Points
                                    ↓
                               Check VIP Upgrade
```

### Inventory Management Flow
```
Order.create() → OrderCreated Event → InventoryHandler
                                    ↓
                               Reserve Stock
                                    ↓
                               Check Reorder Levels
```

This pattern enables complex business workflows while maintaining clean architecture principles and proper separation of concerns.
