<?php
declare(strict_types=1);

namespace Oussema\HideByCountries\Middleware;

use Oussema\HideByCountries\Domain\Model\ApiService;
use Oussema\HideByCountries\Domain\Repository\GeoLocationRepository;
use Oussema\HideByCountries\Domain\Model\IpAddress;
use Oussema\HideByCountries\Domain\Model\CountryCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use Psr\Log\LoggerInterface;


class GeoLocationMiddleware implements MiddlewareInterface
{
    private const COOKIE_NAME = 'user_country';
    private const COOKIE_LIFETIME = 31536000; // 1 year in seconds

    public function __construct(
        private readonly GeoLocationRepository $geoLocationRepository,
        private readonly ExtensionConfiguration $extensionConfiguration,
        private readonly LoggerInterface $logger
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $countryCode = $this->getCountryCode($request);

            // Add country code to request attributes for later use
            $request = $request->withAttribute('country_code', $countryCode);

        } catch (\Exception $e) {
            $this->logger->error('Failed to determine user country', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Handle request and modify the response (PSR-7) to set cookie header
        $response = $handler->handle($request);

        if (isset($countryCode) && $countryCode instanceof CountryCode) {
            $cookieValue = rawurlencode($countryCode->toString());
            $expires = gmdate('D, d-M-Y H:i:s T', time() + self::COOKIE_LIFETIME);
            $cookieHeader = sprintf(
                '%s=%s; Expires=%s; Path=/; Secure; HttpOnly; SameSite=Lax',
                self::COOKIE_NAME,
                $cookieValue,
                $expires
            );

            $response = $response->withAddedHeader('Set-Cookie', $cookieHeader);
        }

        return $response;
    }

    private function getCountryCode(ServerRequestInterface $request): CountryCode
    {
        // First check cookie
        $cookieCountry = $request->getCookieParams()[self::COOKIE_NAME] ?? '';
        if ($cookieCountry !== '') {
            return CountryCode::fromString($cookieCountry);
        }

        // Get IP address
        $ipAddress = $this->getClientIpAddress($request);
        
        // Get country from repository (which handles caching)
        return $this->geoLocationRepository->findCountryForIp($ipAddress);
    }

    private function getClientIpAddress(ServerRequestInterface $request): IpAddress
    {
        $devlopementMode = $this->extensionConfiguration->get('hidebycountries','developemntMode');
        if ($devlopementMode) {
             return  IpAddress::fromString(
            $this->extensionConfiguration->get('hidebycountries', 'publicIpAddressForTesting')??'234.162.28.227'
          );
        }

        return IpAddress::fromString($request->getServerParams()['REMOTE_ADDR']);
    }
}