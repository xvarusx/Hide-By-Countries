<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Domain\Model\Dto;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ExtConfiguration
{
    /** @var bool */
    protected $developemntMode = true;

    /** @var string */
    protected $publicIpAddressForTesting = '234.162.28.227';
    
    /** @var bool */
    protected $isBackendIndicatorEnabled;

    /** @var string */
    protected $classNameSpace = '\Oussema\HideByCountries\Utility\Apis\AetherEpiasGeoLocationService';

    /**@param array $configuration */
    public function __construct(array $configuration = [])
    {
        if (empty($configuration)) {
            try {
                $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
                $configuration = $extensionConfiguration->get('hidebycountries');
            } catch (\Exception) {
                // do nothing
            }
        }
        foreach ($configuration as $key => $value) {
            if (property_exists(self::class, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function getDevelopemntMode(): bool
    {
        return (bool)$this->developemntMode;
    }
    public function getPublicIpAddressForTesting(): string
    {
        return (string)$this->publicIpAddressForTesting;
    }
    public function getClassNameSpace(): string
    {
        return (string)$this->classNameSpace;
    }
    public function isBackendIndicatorEnabled(): bool
    {
        return (bool)($this->isBackendIndicatorEnabled);
    }
}
