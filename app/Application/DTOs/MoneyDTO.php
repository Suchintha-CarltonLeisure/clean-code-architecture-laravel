<?php

namespace App\Application\DTOs;

final class MoneyDTO
{
    public function __construct(
        private float $amount,
        private string $currency = 'USD'
    ) {
        if ($this->amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(MoneyDTO $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot add money with different currencies');
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(MoneyDTO $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot subtract money with different currencies');
        }
        $result = $this->amount - $other->amount;
        if ($result < 0) {
            throw new \InvalidArgumentException('Result cannot be negative');
        }
        return new self($result, $this->currency);
    }

    public function multiply(float $factor): self
    {
        if ($factor < 0) {
            throw new \InvalidArgumentException('Multiplication factor cannot be negative');
        }
        return new self($this->amount * $factor, $this->currency);
    }

    public function format(): string
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'formatted' => $this->format()
        ];
    }
}
