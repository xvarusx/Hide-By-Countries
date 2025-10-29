<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Country\CountryProvider;
class GetCountriesTca
{
    public function loadCountries(&$params): void
    {
        $countryProvider = GeneralUtility::makeInstance(CountryProvider::class);

        $allCountries = $countryProvider->getAll();
        foreach ($allCountries as $country) {
             $params['items'][] = ['label' => $country->getName(), 'value' => $country->getAlpha2IsoCode()];
        }
    }
}
