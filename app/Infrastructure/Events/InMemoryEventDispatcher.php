<?php

namespace App\Infrastructure\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Shared\Events\EventDispatcher;
use App\Domain\Shared\Events\EventHandler;
use Psr\Log\LoggerInterface;

final class InMemoryEventDispatcher implements EventDispatcher
{
    /** @var EventHandler[] */
    private array $handlers = [];

    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function dispatch(DomainEvent $event): void
    {
        $eventName = $event->getEventName();
        
        $this->logger->info('Dispatching domain event', [
            'event_name' => $eventName,
            'aggregate_id' => $event->getAggregateId(),
            'occurred_on' => $event->getOccurredOn()->format('Y-m-d H:i:s')
        ]);

        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($eventName)) {
                try {
                    $handler->handle($event);
                    $this->logger->info('Event handler executed successfully', [
                        'event_name' => $eventName,
                        'handler' => get_class($handler)
                    ]);
                } catch (\Exception $e) {
                    $this->logger->error('Event handler failed', [
                        'event_name' => $eventName,
                        'handler' => get_class($handler),
                        'error' => $e->getMessage()
                    ]);
                    // In production, you might want to implement retry logic or dead letter queues
                }
            }
        }
    }

    public function subscribe(string $eventName, EventHandler $handler): void
    {
        $this->handlers[] = $handler;
        
        $this->logger->info('Event handler subscribed', [
            'event_name' => $eventName,
            'handler' => get_class($handler)
        ]);
    }
}
