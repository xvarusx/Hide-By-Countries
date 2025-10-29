<?php

namespace Oussema\HideByCountries\Domain\Model;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

class ApiService
{
    private String $apiEndPoint;
    private String $apiKey;
    public function __construct(private readonly ExtensionConfiguration $extensionConfiguration)
    {
        $this->setApiService($extensionConfiguration);
    }
    private function setApiService(ExtensionConfiguration $extensionConfiguration): void
    {
        $config = $extensionConfiguration->get('hidebycountries');
        $this->setApiEndPoint($config['apiEndPoint'] ?? 'https://aether.epias.ltd/ip2country/');
        $this->setApiKey($config['apiKey'] ?? '');
    }
    public function getApiEndPoint(): String
    {
        return $this->apiEndPoint;
    }
    public function setApiEndPoint(String $apiEndPoint): void
    {
        if (empty($apiEndPoint) && filter_var($apiEndPoint, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Service URL cannot be empty and must be a valid URL');
        }
        $this->apiEndPoint = $apiEndPoint;

    }
    public function getApiKey(): String
    {
        return $this->apiKey;
    }
    public function setApiKey(String $apiKey): void
    {
        $this->apiKey = $apiKey;
    }
}
