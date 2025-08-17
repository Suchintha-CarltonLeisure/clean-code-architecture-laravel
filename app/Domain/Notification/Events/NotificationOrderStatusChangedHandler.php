<?php

namespace App\Domain\Notification\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Shared\Events\EventHandler;
use App\Domain\Order\Events\OrderStatusChanged;
use Psr\Log\LoggerInterface;

/**
 * Handles order status changes to send notifications
 * This demonstrates cross-aggregate communication between Order and Notification
 */
final class NotificationOrderStatusChangedHandler implements EventHandler
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handle(DomainEvent $event): void
    {
        if (!$event instanceof OrderStatusChanged) {
            return;
        }

        // In a real application, this would:
        // 1. Determine notification preferences for the customer
        // 2. Send email/SMS notifications based on status change
        // 3. Create in-app notifications
        // 4. Log notification history

        $statusMessages = [
            'confirmed' => 'Your order has been confirmed and is being prepared.',
            'shipped' => 'Great news! Your order has been shipped and is on its way.',
            'delivered' => 'Your order has been delivered. Thank you for your business!',
            'cancelled' => 'Your order has been cancelled. If you have questions, please contact support.'
        ];

        $message = $statusMessages[$event->getNewStatus()->getValue()] ?? 'Your order status has been updated.';

        $this->logger->info('Order status notification sent', [
            'order_id' => $event->getOrderId()->getValue(),
            'previous_status' => $event->getPreviousStatus()->getValue(),
            'new_status' => $event->getNewStatus()->getValue(),
            'notification_message' => $message
        ]);

        // Example business logic that would be implemented:
        // - Send email notification to customer
        // - Send SMS if customer has opted in
        // - Create push notification for mobile app
        // - Update customer communication history
    }

    public function canHandle(string $eventName): bool
    {
        return $eventName === 'order.status_changed';
    }
}
