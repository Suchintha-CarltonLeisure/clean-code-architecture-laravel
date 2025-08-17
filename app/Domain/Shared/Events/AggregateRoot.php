<?php

namespace App\Domain\Shared\Events;

abstract class AggregateRoot
{
    /** @var DomainEvent[] */
    private array $domainEvents = [];

    protected function recordEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * @return DomainEvent[]
     */
    public function getUncommittedEvents(): array
    {
        return $this->domainEvents;
    }

    public function markEventsAsCommitted(): void
    {
        $this->domainEvents = [];
    }

    public function hasUncommittedEvents(): bool
    {
        return !empty($this->domainEvents);
    }
}
