<?php
declare(strict_types=1);

namespace Oussema\HideByCountries\Domain\Model;

use InvalidArgumentException;

final class IpAddress
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
        if($value === '' || $value == '127.0.0.1') {
            $this->value = '234.162.28.227';
        }elseif (!filter_var($value, FILTER_VALIDATE_IP)){
            throw new InvalidArgumentException('Invalid IP address format');
        }else{
            $this->value = $value;
        }
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(IpAddress $other): bool
    {
        return $this->value === $other->value;
    }
}