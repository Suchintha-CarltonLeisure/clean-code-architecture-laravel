<?php

namespace App\Domain\Shared\Events;

use DateTimeImmutable;

interface DomainEvent
{
    public function getAggregateId(): string;
    public function getEventName(): string;
    public function getOccurredOn(): DateTimeImmutable;
    public function getEventData(): array;
}
