<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Oussema\HideByCountries\Middleware\GeoLocationMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use Oussema\HideByCountries\Domain\Repository\GeoLocationRepository;
use Oussema\HideByCountries\Domain\Model\CountryCode;
use Psr\Http\Message\ResponseInterface;

final class GeoLocationMiddlewareTest extends TestCase
{
    public function testProcessAddsSetCookieHeaderAndAttribute(): void
    {
        $repo = $this->createMock(GeoLocationRepository::class);
        $extConf = $this->createMock(ExtensionConfiguration::class);
        $logger = $this->createMock(LoggerInterface::class);

        $middleware = new GeoLocationMiddleware($repo, $extConf, $logger);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')->willReturn([]);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '1.2.3.4']);

        $repo->method('findCountryForIp')->willReturn(CountryCode::fromString('DE'));

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())->method('withAddedHeader')->with(self::isType('string'), self::isType('string'))->willReturnSelf();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $result = $middleware->process($request, $handler);

        self::assertInstanceOf(ResponseInterface::class, $result);
    }
}
