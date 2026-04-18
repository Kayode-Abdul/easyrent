<?php

namespace App\Helpers;

/**
 * Helper class to handle money formatting with method chaining support
 */
class MoneyAmount implements \JsonSerializable, \Stringable
{
    protected $amount;
    protected $symbol;
    protected $formatted;

    public function __construct($amount, $symbol)
    {
        $this->amount = $amount;
        $this->symbol = $symbol;
        $this->formatted = number_format($amount, 2);
    }

    public function getSymbol()
    {
        return $this->symbol;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function __toString(): string
    {
        return $this->symbol . $this->formatted;
    }

    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
}

