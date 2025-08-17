<?php

namespace App\Domain\Customer\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Shared\Events\EventHandler;
use App\Domain\Order\Events\OrderCreated;
use Psr\Log\LoggerInterface;

/**
 * Handles order creation events to update customer statistics
 * This demonstrates cross-aggregate communication between Order and Customer
 */
final class CustomerOrderCreatedHandler implements EventHandler
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
        // 1. Find the Customer aggregate
        // 2. Update customer order count and total spent
        // 3. Check for loyalty tier upgrades
        // 4. Save the updated Customer aggregate

        $this->logger->info('Customer order statistics updated', [
            'customer_name' => $event->getCustomerName()->getValue(),
            'order_id' => $event->getOrderId()->getValue(),
            'order_amount' => $event->getTotalAmount()->getAmount(),
            'item_count' => $event->getItemCount()
        ]);

        // Example business logic that would be implemented:
        // - Update customer lifetime value
        // - Check if customer qualifies for VIP status
        // - Update purchase history
        // - Trigger loyalty point calculations
    }

    public function canHandle(string $eventName): bool
    {
        return $eventName === 'order.created';
    }
}
