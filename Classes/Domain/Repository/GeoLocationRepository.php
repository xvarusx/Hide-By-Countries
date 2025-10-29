<?php
declare(strict_types=1);

namespace Oussema\HideByCountries\Domain\Repository;

use Oussema\HideByCountries\Domain\Model\ApiService;
use Oussema\HideByCountries\Domain\Model\IpAddress;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use Oussema\HideByCountries\Domain\Model\CountryCode;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use Oussema\HideByCountries\Utility\Apis\GeoLocationApiInterface;

class GeoLocationRepository
{
    private const CACHE_LIFETIME = 86400;

    public function __construct(
        private readonly GeoLocationApiInterface $geoLocationService,
        private readonly FrontendInterface $cache
    ) {}

    public function findCountryForIp(IpAddress $ipAddress): CountryCode
    {
        $cacheIdentifier = 'geoip_' . $ipAddress->toString();
        
        if ($this->cache->has($cacheIdentifier)) {
            return CountryCode::fromString($this->cache->get($cacheIdentifier));
        }

        $countryCode = $this->geoLocationService->getCountryForIp($ipAddress);
        
        $this->cache->set(
            $cacheIdentifier,
            $countryCode->toString(),
            [],
            self::CACHE_LIFETIME
        );

        return $countryCode;
    }
}