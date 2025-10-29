<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Oussema\HideByCountries\Domain\Model\CountryCode;

class CountryCodeTest extends TestCase
{
    public function testCountryCodeFormat(): void
    {

        $countryCodes = ['US', 'FR', 'DE', 'JP', 'IN', 'USA', 'tn'];
        foreach ($countryCodes as $code) {
            self::assertMatchesRegularExpression('/^[A-Z]{2}$/', $code, "Country code {$code} is not in the correct format.");
        }
    }
    public function testInvalidFormatThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CountryCode::fromString('USA');
    }
    public function testEmptyCountryCodeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CountryCode::fromString('');
    }
}
