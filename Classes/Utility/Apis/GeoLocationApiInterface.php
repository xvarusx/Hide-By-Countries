<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Utility\Apis;

interface GeoLocationApiInterface
{
    /**
     * Get the country code for a given IP address
     *
     * @param string $ipAddress
     * @return string
     * @throws GeoLocationException If the service fails to retrieve the country code
     */
    public function getCountryForIp(string $ipAddress): string;
}
