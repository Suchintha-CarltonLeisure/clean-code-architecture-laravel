<?php

namespace App\Domain\Order\ValueObjects;

final class OrderStatus
{
      public const PENDING = 'pending';
      public const CONFIRMED = 'confirmed';
      public const SHIPPED = 'shipped';
      public const DELIVERED = 'delivered';
      public const CANCELLED = 'cancelled';

      private static array $validStatuses = [
            self::PENDING,
            self::CONFIRMED,
            self::SHIPPED,
            self::DELIVERED,
            self::CANCELLED,
      ];

      private function __construct(
            private readonly string $value
      ) {
            if (!in_array($this->value, self::$validStatuses, true)) {
                  throw new \InvalidArgumentException(
                        sprintf(
                              'Invalid order status: %s. Valid statuses are: %s',
                              $this->value,
                              implode(', ', self::$validStatuses)
                        )
                  );
            }
      }

      public static function fromString(string $value): self
      {
            return new self($value);
      }

      public static function pending(): self
      {
            return new self(self::PENDING);
      }

      public static function confirmed(): self
      {
            return new self(self::CONFIRMED);
      }

      public static function shipped(): self
      {
            return new self(self::SHIPPED);
      }

      public static function delivered(): self
      {
            return new self(self::DELIVERED);
      }

      public static function cancelled(): self
      {
            return new self(self::CANCELLED);
      }

      public function getValue(): string
      {
            return $this->value;
      }

      public function isPending(): bool
      {
            return $this->value === self::PENDING;
      }

      public function isConfirmed(): bool
      {
            return $this->value === self::CONFIRMED;
      }

      public function isShipped(): bool
      {
            return $this->value === self::SHIPPED;
      }

      public function isDelivered(): bool
      {
            return $this->value === self::DELIVERED;
      }

      public function isCancelled(): bool
      {
            return $this->value === self::CANCELLED;
      }

      public function canTransitionTo(OrderStatus $newStatus): bool
      {
            $transitions = [
                  self::PENDING => [self::CONFIRMED, self::CANCELLED],
                  self::CONFIRMED => [self::SHIPPED, self::CANCELLED],
                  self::SHIPPED => [self::DELIVERED],
                  self::DELIVERED => [],
                  self::CANCELLED => [],
            ];

            return in_array($newStatus->getValue(), $transitions[$this->value] ?? [], true);
      }

      public function equals(OrderStatus $other): bool
      {
            return $this->value === $other->value;
      }

      public function __toString(): string
      {
            return $this->value;
      }
}