<?php

namespace App\Domain\Order\ValueObjects;

use App\Domain\Order\Exceptions\InvalidCustomerNameException;

final class CustomerName
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty(trim($value))) {
            throw new InvalidCustomerNameException('Customer name cannot be empty');
        }
        $this->value = trim($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(CustomerName $other): bool
    {
        return $this->value === $other->value;
    }
}
