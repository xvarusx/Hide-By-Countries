<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Tests\Functional;

class FakeGeoLocationService implements \Oussema\HideByCountries\Utility\Apis\GeoLocationServiceInterface
{
    public function getCountryForIp(string $ipAddress): string
    {
        // Return fixed responses for testing
        return match ($ipAddress) {
            '8.8.8.8' => 'US',
            '93.184.216.34' => 'DE',
            '127.0.0.1' => 'ZZ',
            default => 'DE',
        };
    }
}
