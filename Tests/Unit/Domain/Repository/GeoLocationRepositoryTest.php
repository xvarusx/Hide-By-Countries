<?php

namespace Oussema\HideByCountries\Tests\Unit\Domain\Repository;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use Oussema\HideByCountries\Domain\Model\CountryCode;
use Oussema\HideByCountries\Utility\Apis\GeoLocationApiInterface;
use Oussema\HideByCountries\Domain\Repository\GeoLocationRepository;
use Oussema\HideByCountries\Domain\Model\IpAddress;

final class GeoLocationRepositoryTest extends UnitTestCase
{
    protected FrontendInterface $cache;
    protected GeoLocationApiInterface $service;
    protected GeoLocationRepository $repository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = $this->createMock(FrontendInterface::class);
        $this->service = $this->createMock(GeoLocationApiInterface::class);
        $this->repository = new GeoLocationRepository($this->service, $this->cache);
    }

    public function testReturnsFromCache(): void
    {
        $ip = IpAddress::fromString('1.2.3.4');
        $this->cache->expects(self::once())->method('has')->willReturn(true);
        $this->cache->expects(self::once())->method('get')->willReturn('US');

        $result = $this->repository->findCountryForIp($ip);

        self::assertSame('US', $result->toString());

    }

    public function testFetchesAndCachesIfMissing(): void
    {
        $ip = IpAddress::fromString('1.2.3.4');
        $this->cache->expects(self::once())->method('has')->willReturn(false);
        $this->service->expects(self::once())->method('getCountryForIp')->willReturn(CountryCode::fromString('FR'));
        $this->cache->expects(self::once())->method('set');

        $result = $this->repository->findCountryForIp($ip);
        self::assertSame('FR', $result->toString());
    }

}
