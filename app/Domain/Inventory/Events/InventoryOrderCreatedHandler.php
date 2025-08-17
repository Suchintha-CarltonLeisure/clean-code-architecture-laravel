<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Shared\Events\EventHandler;
use App\Domain\Order\Events\OrderCreated;
use Psr\Log\LoggerInterface;

/**
 * Handles order creation events to update inventory levels
 * This demonstrates cross-aggregate communication between Order and Inventory
 */
final class InventoryOrderCreatedHandler implements EventHandler
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handle(DomainEvent $event): void
    {
        if (!$event instanceof OrderCreated) {
            return;
        }

        // In a real application, this would:
        // 1. Find inventory items for each order item
        // 2. Reserve stock for the order
        // 3. Update available quantities
        // 4. Trigger reorder alerts if stock is low

        $this->logger->info('Inventory reservation processed for order', [
            'order_id' => $event->getOrderId()->getValue(),
            'customer' => $event->getCustomerName()->getValue(),
            'item_count' => $event->getItemCount()
        ]);

        // Example business logic that would be implemented:
        // - Reserve inventory for order items
        // - Check stock levels and trigger reorder alerts
        // - Update product availability status
        // - Calculate estimated delivery dates based on stock
    }

    public function canHandle(string $eventName): bool
    {
        return $eventName === 'order.created';
    }
}
