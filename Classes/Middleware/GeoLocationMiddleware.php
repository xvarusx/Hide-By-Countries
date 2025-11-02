<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Oussema\HideByCountries\Domain\Repository\GeoLocationRepository;
use Oussema\HideByCountries\Domain\Model\Dto\ExtConfiguration;
use Oussema\HideByCountries\Utility\SessionManagementUtility;

class GeoLocationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ExtConfiguration $extensionConfiguration,
        private readonly GeoLocationRepository $geoLocationRepository,
        private readonly SessionManagementUtility $sessionManagement,
    ) {
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->setCountryCookie($request);
        return  $handler->handle($request);
    }

    private function setCountryCookie(ServerRequestInterface $request): void
    {
        if (is_null($this->sessionManagement->getCountryFromSession($request))) {
            $ipAddress = $this->getClientPublicIpAddress($request);
            $countryCode = $this->geoLocationRepository->findCountryForIp($ipAddress);
            $this->sessionManagement->storeCountryInSession($countryCode,$request);
        }
    }

    private function getClientPublicIpAddress(ServerRequestInterface $request): string
    {
        $devlopementMode = (bool)$this->extensionConfiguration->getDevelopemntMode();
        if ($devlopementMode) {
            return  $this->extensionConfiguration->getPublicIpAddressForTesting();
        }
        return $request->getServerParams()['REMOTE_ADDR'];
    }
}

