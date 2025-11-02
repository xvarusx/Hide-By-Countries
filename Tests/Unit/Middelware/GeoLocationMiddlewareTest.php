<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Tests\Unit\Middelware;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Oussema\HideByCountries\Middleware\GeoLocationMiddleware;
use Oussema\HideByCountries\Domain\Repository\GeoLocationRepository;
use Oussema\HideByCountries\Domain\Model\CountryCode;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

final class GeoLocationMiddlewareTest extends UnitTestCase
{
    private GeoLocationRepository $geoLocationRepository;
    private ExtensionConfiguration $extensionConfiguration;
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;
    private ResponseInterface $response;
    private GeoLocationMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for dependencies
        $this->geoLocationRepository = $this->createMock(GeoLocationRepository::class);
        $this->extensionConfiguration = $this->createMock(ExtensionConfiguration::class);

        // Create request and response mocks
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);

        // Configure request mock with needed methods
        $this->request->method('getCookieParams')->willReturn([]);
        $this->request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);
        $this->request->method('withAttribute')->willReturn($this->request);

        // Create handler mock
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->handler->method('handle')->willReturn($this->response);

        // Create middleware instance with mocked dependencies
        $this->middleware = new GeoLocationMiddleware(
            $this->geoLocationRepository,
            $this->extensionConfiguration
        );
    }

    public function testProcessAddsSetCookieHeaderAndAttribute(): void
    {
        // Configure mocks for this specific test
        $countryCode = CountryCode::fromString('US');

        // Configure GeoLocationRepository mock
        $this->geoLocationRepository
            ->method('findCountryForIp')
            ->willReturn($countryCode);

        // Configure ExtensionConfiguration mock
        $this->extensionConfiguration
            ->method('get')
            ->willReturnMap([
                ['hidebycountries', 'developemntMode', false],
                ['hidebycountries', 'publicIpAddressForTesting', null],
            ]);

        // Configure response mock for header addition
        $this->response
            ->expects(self::once())
            ->method('withAddedHeader')
            ->with(
                'Set-Cookie',
                self::stringContains('user_country=US')
            )
            ->willReturn($this->response);

        // Execute middleware
        $result = $this->middleware->process($this->request, $this->handler);

        // Assertions
        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame($this->response, $result);
    }

    public function testProcessHandlesExceptionGracefully(): void
    {
        // Configure GeoLocationRepository to throw an exception
        $this->geoLocationRepository
            ->method('findCountryForIp')
            ->willThrowException(new \Exception('API Error'));


        // Execute middleware
        $result = $this->middleware->process($this->request, $this->handler);

        // Should still return response even after error
        self::assertInstanceOf(ResponseInterface::class, $result);
    }
}
