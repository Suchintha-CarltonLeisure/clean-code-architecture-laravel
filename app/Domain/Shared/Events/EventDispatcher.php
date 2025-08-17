<?php

namespace App\Domain\Shared\Events;

interface EventDispatcher
{
    public function dispatch(DomainEvent $event): void;
    public function subscribe(string $eventName, EventHandler $handler): void;
}
