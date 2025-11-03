<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Tests\Unit\Middleware;

use Oussema\HideByCountries\Domain\Model\Dto\ExtConfiguration;
use Oussema\HideByCountries\Domain\Repository\GeoLocationRepository;
use Oussema\HideByCountries\Middleware\GeoLocationMiddleware;
use Oussema\HideByCountries\Utility\SessionManagementUtility;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class GeoLocationMiddlewareTest extends UnitTestCase
{
    private GeoLocationMiddleware $subject;
    private ExtConfiguration|MockObject $extConfigMock;
    private GeoLocationRepository|MockObject $geoRepoMock;
    private SessionManagementUtility|MockObject $sessionMock;
    private ServerRequestInterface|MockObject $requestMock;
    private RequestHandlerInterface|MockObject $handlerMock;
    private ResponseInterface|MockObject $responseMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extConfigMock = $this->createMock(ExtConfiguration::class);
        $this->geoRepoMock = $this->createMock(GeoLocationRepository::class);
        $this->sessionMock = $this->createMock(SessionManagementUtility::class);
        $this->requestMock = $this->createMock(ServerRequestInterface::class);
        $this->handlerMock = $this->createMock(RequestHandlerInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->subject = new GeoLocationMiddleware(
            $this->extConfigMock,
            $this->geoRepoMock,
            $this->sessionMock
        );
    }

    /**
     * @test
     */
    public function processDoesNothingIfSessionAlreadyHasCountry(): void
    {
        $this->sessionMock
            ->expects(self::once())
            ->method('getCountryFromSession')
            ->with($this->requestMock)
            ->willReturn('FR');

        $this->geoRepoMock
            ->expects(self::never())
            ->method('findCountryForIp');

        $this->handlerMock
            ->expects(self::once())
            ->method('handle')
            ->with($this->requestMock)
            ->willReturn($this->responseMock);

        $response = $this->subject->process($this->requestMock, $this->handlerMock);
        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @test
     */
    public function processStoresCountryInSessionIfNotAlreadySet(): void
    {
        $ipAddress = '8.8.8.8';
        $countryCode = 'US';

        $this->sessionMock
            ->expects(self::once())
            ->method('getCountryFromSession')
            ->with($this->requestMock)
            ->willReturn(null);

        $this->extConfigMock
            ->method('getDevelopemntMode')
            ->willReturn(false);

        $this->requestMock
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => $ipAddress]);

        $this->geoRepoMock
            ->expects(self::once())
            ->method('findCountryForIp')
            ->with($ipAddress)
            ->willReturn($countryCode);

        $this->sessionMock
            ->expects(self::once())
            ->method('storeCountryInSession')
            ->with($countryCode, $this->requestMock);

        $this->handlerMock
            ->expects(self::once())
            ->method('handle')
            ->willReturn($this->responseMock);

        $response = $this->subject->process($this->requestMock, $this->handlerMock);
        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @test
     */
    public function processUsesDevelopmentModeIpWhenEnabled(): void
    {
        $testIp = '234.162.28.227';
        $countryCode = 'FR';

        $this->sessionMock
            ->expects(self::once())
            ->method('getCountryFromSession')
            ->willReturn(null);

        $this->extConfigMock
            ->method('getDevelopemntMode')
            ->willReturn(true);

        $this->extConfigMock
            ->method('getPublicIpAddressForTesting')
            ->willReturn($testIp);

        $this->geoRepoMock
            ->expects(self::once())
            ->method('findCountryForIp')
            ->with($testIp)
            ->willReturn($countryCode);

        $this->sessionMock
            ->expects(self::once())
            ->method('storeCountryInSession')
            ->with($countryCode, $this->requestMock);

        $this->handlerMock
            ->expects(self::once())
            ->method('handle')
            ->willReturn($this->responseMock);

        $response = $this->subject->process($this->requestMock, $this->handlerMock);
        self::assertInstanceOf(ResponseInterface::class, $response);
    }
}
