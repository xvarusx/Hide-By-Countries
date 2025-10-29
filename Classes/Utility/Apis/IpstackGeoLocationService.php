<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Services\Apis;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use GuzzleHttp\Exception\GuzzleException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Oussema\HideByCountries\Domain\Model\IpAddress;
use Oussema\HideByCountries\Domain\Model\ApiService;
use Oussema\HideByCountries\Domain\Model\CountryCode;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use Oussema\HideByCountries\Utility\GeoLocationException;

class IpstackGeoLocationService implements GeoLocationApiInterface
{
    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly ExtensionConfiguration $extensionConfiguration,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public function getCountryForIp(IpAddress $ipAddress): CountryCode
    {
        $apiService = GeneralUtility::makeInstance(ApiService::class, $this->extensionConfiguration);
        try {
            $response = $this->requestFactory->request(
                $apiService->getApiEndPoint() . $ipAddress->toString(),
                'GET',
                [
                    'headers' => [
                        'access-control-allow-origin' => '*',
                        'access_key' => $apiService->getApiKey(),
                    ],
                ]
            );

            if ($response->getStatusCode() !== 200) {
                throw GeoLocationException::fromApiError(
                    sprintf('API returned status code %d', $response->getStatusCode())
                );
            }

            $content = trim($response->getBody()->getContents());

            return CountryCode::fromString($content);

        } catch (GuzzleException $e) {
            $this->logger?->error('GeoLocation API request failed', [
                'ip' => $ipAddress->toString(),
                'error' => $e->getMessage(),
            ]);
            throw GeoLocationException::fromApiError($e->getMessage(), 0, $e);
        } catch (\InvalidArgumentException $e) {
            throw GeoLocationException::fromInvalidResponse($e->getMessage(), 0, $e);
        }
    }
}
