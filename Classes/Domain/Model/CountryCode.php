<?php
declare(strict_types=1);

namespace Oussema\HideByCountries\Domain\Model;

use InvalidArgumentException;

final class CountryCode
{
    private string $value;

    private function __construct(string $value)
    {
        $this->setValue($value);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function setValue(string $value): void
    {
        if ($value === '') {
            throw new InvalidArgumentException('Country code cannot be empty');
        }

        if (!preg_match('/^[A-Z]{2}$/', strtoupper($value))) {
            throw new InvalidArgumentException('Invalid country code format. Must be 2 uppercase letters.');
        }

        $this->value = strtoupper($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(CountryCode $other): bool
    {
        return $this->value === $other->value;
    }
}