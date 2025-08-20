<?php

namespace App\Domain\Order\ValueObjects;

final class CustomerName
{
      private function __construct(
            private readonly string $value
      ) {
            $this->validate($this->value);
      }

      public static function fromString(string $value): self
      {
            return new self($value);
      }

      public function getValue(): string
      {
            return $this->value;
      }

      public function getFirstName(): string
      {
            $parts = explode(' ', trim($this->value));
            return $parts[0] ?? '';
      }

      public function getLastName(): string
      {
            $parts = explode(' ', trim($this->value));
            return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
      }

      public function getInitials(): string
      {
            $parts = explode(' ', trim($this->value));
            return implode('.', array_map(fn($part) => strtoupper(substr($part, 0, 1)), $parts)) . '.';
      }

      public function equals(CustomerName $other): bool
      {
            return strtolower(trim($this->value)) === strtolower(trim($other->value));
      }

      public function __toString(): string
      {
            return $this->value;
      }

      private function validate(string $value): void
      {
            $trimmed = trim($value);

            if (empty($trimmed)) {
                  throw new \InvalidArgumentException('Customer name cannot be empty');
            }

            if (strlen($trimmed) < 2) {
                  throw new \InvalidArgumentException('Customer name must be at least 2 characters long');
            }

            if (strlen($trimmed) > 100) {
                  throw new \InvalidArgumentException('Customer name cannot exceed 100 characters');
            }

            // if (!preg_match('/^[a-zA-Z\s\'-]+$/', $trimmed)) {
            //       throw new \InvalidArgumentException('Customer name can only contain letters, spaces, hyphens, and apostrophes');
            // }
      }
}
