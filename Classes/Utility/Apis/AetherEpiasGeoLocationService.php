<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Utility\Apis;

use GuzzleHttp\Exception\GuzzleException;
use Oussema\HideByCountries\Domain\Model\Dto\CountryCode;
use Oussema\HideByCountries\Domain\Model\IpAddress;
use Oussema\HideByCountries\Utility\GeoLocationException;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class AetherEpiasGeoLocationService implements GeoLocationApiInterface
{
    
    public const ENDPOINT = 'https://aether.epias.ltd/ip2country/';
    public function __construct(
        private readonly RequestFactory $requestFactory,
    ) {}

    public function getCountryForIp(string $ipAddress): string
    {

        try {
            $response = $this->requestFactory->request(
                self::ENDPOINT. $ipAddress,
                'GET',
                [
                    'headers' => [
                        'access-control-allow-origin' => '*',
                    ],
                ]
            );

            if ($response->getStatusCode() !== 200) {
                throw GeoLocationException::fromApiError(
                    sprintf('API returned status code %d', $response->getStatusCode())
                );
            }

            $content = trim($response->getBody()->getContents());

            return $content;

        } catch (GuzzleException $e) {
            throw GeoLocationException::fromApiError($e->getMessage(), 0, $e);
        } catch (\InvalidArgumentException $e) {
            throw GeoLocationException::fromInvalidResponse($e->getMessage(), 0, $e);
        }
    }
}
