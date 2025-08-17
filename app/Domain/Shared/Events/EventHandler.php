<?php

namespace App\Domain\Shared\Events;

interface EventHandler
{
    public function handle(DomainEvent $event): void;
    public function canHandle(string $eventName): bool;
}
