<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Tests\Functional;

use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

/**
 * Functional tests for the Hide-By-Countries extension
 * Updated to simulate country detection via middleware cookies.
 */
final class HideByCountriesFunctionalTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/hidebycountries',
    ];
    protected array $coreExtensionsToLoad = [
        'core',
        'frontend',
        'fluid',
    ];
    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/sys_template.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tt_content.csv');


        // Create minimal root TypoScript configuration

        $this->setUpFrontendRootPage(
            1,
            ['EXT:hidebycountries/Tests/Functional/Fixtures/TypoScript/setup.typoscript'],
        );

        $this->createSiteConfiguration();

    }
    /**
         * Create site configuration for testing
         */
    protected function createSiteConfiguration(): void
    {
        $siteConfigPath = $this->instancePath . '/typo3conf/sites/testing';
        GeneralUtility::mkdir_deep($siteConfigPath);

        $configuration = [
            'rootPageId' => 1,
            'base' => 'https://localhost/',
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'English',
                    'enabled' => true,
                    'base' => '/',
                    'locale' => 'en_US.UTF-8',
                    'navigationTitle' => 'English',
                    'flag' => 'us',
                ],
            ],
            'routes' => [],
        ];

        $yamlFileContents = \Symfony\Component\Yaml\Yaml::dump($configuration, 99, 2);
        GeneralUtility::writeFile($siteConfigPath . '/config.yaml', $yamlFileContents);
    }
    /**
        * Helper: get frontend content with simulated cookie value
        */
    protected function getFrontendResponseForCountry(?string $countryCode): string
    {
        // Build an InternalRequest with cookie
        $request = new InternalRequest('https://localhost/');
        $request = $request->withPageId(1);

        if ($countryCode !== null) {
            $request = $request->withAddedHeader('Cookie', 'user_country=' . $countryCode);
        }

        $response = $this->executeFrontendSubRequest($request);
        return (string)$response->getBody();
    }

    /** @test */
    public function contentElementIsHiddenForMatchingCountry(): void
    {
        $content = $this->getFrontendResponseForCountry('FR');

        self::assertStringNotContainsString('CE_HIDDEN_FR', $content, 'Hidden element should not render for FR');
        self::assertStringContainsString('CE_VISIBLE_GLOBAL', $content, 'Visible element should render normally');
    }

    /** @test */
    public function contentElementIsVisibleForDifferentCountry(): void
    {
        $content = $this->getFrontendResponseForCountry('DE');

        self::assertStringContainsString('CE_HIDDEN_FR', $content, 'Element hidden only for FR should render for DE');
    }

    /** @test */
    public function multipleCountriesHideElementCorrectly(): void
    {
        $content = $this->getFrontendResponseForCountry('US');

        self::assertStringNotContainsString('MULTI_COUNTRY_CE', $content);
    }

    /** @test */
    public function lowercaseCountryCodeHandledProperly(): void
    {
        $content = $this->getFrontendResponseForCountry('fr');

        self::assertStringNotContainsString('CE_HIDDEN_FR', $content, 'Lowercase country cookie should still hide the element');
    }

    /** @test */
    public function cacheVariesByCountry(): void
    {
        $fr = $this->getFrontendResponseForCountry('FR');
        $de = $this->getFrontendResponseForCountry('DE');

        self::assertNotSame($fr, $de, 'Cache should vary per country');
    }
}
