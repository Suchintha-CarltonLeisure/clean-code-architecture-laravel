<?php

namespace App\Domain\Order\ValueObjects;

final class OrderId
{
      private function __construct(
            private readonly ?int $value
      ) {
            if ($this->value !== null && $this->value <= 0) {
                  throw new \InvalidArgumentException('Order ID must be positive or null');
            }
      }

      public static function fromInt(?int $value): self
      {
            return new self($value);
      }

      public static function generate(): self
      {
            return new self(null);
      }

      public function getValue(): ?int
      {
            return $this->value;
      }

      public function isNull(): bool
      {
            return $this->value === null;
      }

      public function isNotNull(): bool
      {
            return $this->value !== null;
      }

      public function equals(OrderId $other): bool
      {
            return $this->value === $other->value;
      }

      public function __toString(): string
      {
            return (string) $this->value;
      }
}