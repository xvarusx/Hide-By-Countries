<?php
declare(strict_types=1);

namespace Oussema\HideByCountries\Utility\Apis;

use Oussema\HideByCountries\Domain\Model\IpAddress;
use Oussema\HideByCountries\Domain\Model\CountryCode;

interface GeoLocationApiInterface
{
    /**
     * Get the country code for a given IP address
     *
     * @param IpAddress $ipAddress
     * @return CountryCode
     * @throws GeoLocationException If the service fails to retrieve the country code
     */
    public function getCountryForIp(IpAddress $ipAddress): CountryCode;
}