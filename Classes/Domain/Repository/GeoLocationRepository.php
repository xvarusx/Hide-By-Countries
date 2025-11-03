<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Domain\Repository;

use Oussema\HideByCountries\Domain\Model\Dto\ExtConfiguration;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeoLocationRepository
{
    private const CACHE_LIFETIME = 86400;

    public function __construct(
        private readonly FrontendInterface $cache,
        private readonly ExtConfiguration $extensionConfiguration,
    ) {}

    public function findCountryForIp(string $ipAddress): string
    {
        $cacheIdentifier =  sha1('geoip_' . $ipAddress);
        $value = $this->cache->get($cacheIdentifier);
        if ($value != false) {
            return $value;
        }
        $classNameSpace = $this->extensionConfiguration->getClassNameSpace();

        if (empty($classNameSpace) || !class_exists($classNameSpace)) {
            $classNameSpace = 'Oussema\HideByCountries\Utility\Apis\AetherEpiasGeoLocationService';
        }

        $countryCode = GeneralUtility::makeInstance($classNameSpace)->getCountryForIp($ipAddress);

        $this->cache->set(
            $cacheIdentifier,
            $countryCode,
            [],
            self::CACHE_LIFETIME
        );
        return $countryCode;
    }
}
