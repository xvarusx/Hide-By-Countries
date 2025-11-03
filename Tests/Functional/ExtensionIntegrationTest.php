<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Tests\Functional;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional test for Hide By Countries extension integration
 */
class ExtensionIntegrationTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/hidebycountries',
    ];

    protected array $coreExtensionsToLoad = [
        'frontend',
    ];
    // protected array $pathsToProvideInTestInstance = [
    //     'typo3conf/ext/hidebycountries/Tests/Functional/Fixtures/Sites/' => 'typo3conf/sites/',
    // ];
    protected function createSiteConfiguration(string $siteIdentifier = 'testing'): void
    {
        $siteConfigPath = $this->instancePath . '/typo3conf/sites/' . $siteIdentifier;
        GeneralUtility::mkdir_deep($siteConfigPath);

        $configuration = [
            'rootPageId' => 1,
            'base' => 'http://localhost/',
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
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['hidebycountries'] ??= [
            'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
            'backend'  => \TYPO3\CMS\Core\Cache\Backend\NullBackend::class,
        ];

        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tt_content.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/be_users.csv');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:hidebycountries/Tests/Functional/Fixtures/TypoScript/setup.typoscript',
            ]
        );

        $this->createSiteConfiguration('testing');

        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['hidebycountries']['classNameSpace'] =
              \Oussema\HideByCountries\Tests\Functional\FakeGeoLocationService::class;
        echo 'Fake class registered: ' . $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['hidebycountries']['classNameSpace'] . PHP_EOL;

    }

    /**
     * @test
     */
    public function extensionIsLoaded(): void
    {
        $packageManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Package\PackageManager::class);
        self::assertTrue($packageManager->isPackageActive('hidebycountries'));
    }

    /**
     * @test
     */
    public function databaseTableHasCountryRestrictionField(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        $schemaManager = $connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tt_content');

        self::assertArrayHasKey('tx_hidebycountries', $columns);
    }

    /**
     * @test
     */
    public function contentElementCanBeStoredWithCountryRestriction(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        $connection->insert(
            'tt_content',
            [
                'pid' => 1,
                'uid' => 999,
                'CType' => 'text',
                'header' => 'Test Content',
                'tx_hidebycountries' => 'DE,FR,IT',
                'tstamp' => time(),
                'crdate' => time(),
            ]
        );

        $queryBuilder = $connection->createQueryBuilder();
        $result = $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter(999, \TYPO3\CMS\Core\Database\Connection::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();

        self::assertIsArray($result);
        self::assertEquals('DE,FR,IT', $result['tx_hidebycountries']);
    }

    /**
     * @test
     */
    // public function contentElementWithoutRestrictionIsRendered(): void
    // {
    //     $_COOKIE['fe_user_country'] = 'DE';

    //     $response = $this->executeFrontendSubRequest(
    //         (new \TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest('http://localhost/'))
    //     );

    //     $content = (string) $response->getBody();

    //     // Should contain content without restrictions
    //     self::assertStringContainsString('No Restrictions', $content);

    //     unset($_COOKIE['fe_user_country']);
    // }

    /**
     * @test
     */
    // public function contentElementIsHiddenForRestrictedCountry(): void
    // {
    //     $_COOKIE['fe_user_country'] = 'US';

    //     $response = $this->executeFrontendSubRequest(
    //         (new \TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest('http://localhost/'))
    //     );

    //     $content = (string) $response->getBody();

    //     // Should not contain DE-only content
    //     self::assertStringNotContainsString('Only for DE', $content);

    //     unset($_COOKIE['fe_user_country']);
    // }

    /**
     * @test
     */
    // public function contentElementIsShownForAllowedCountry(): void
    // {
    //     $_COOKIE['fe_user_country'] = 'DE';

    //     $response = $this->executeFrontendSubRequest(
    //         (new \TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest('http://localhost/'))
    //     );

    //     $content = (string) $response->getBody();

    //     // Should contain DE-allowed content
    //     self::assertStringContainsString('Available in DE', $content);

    //     unset($_COOKIE['fe_user_country']);
    // }

    /**
     * @test
     */
    // public function middlewareSetsCookieOnFirstVisit(): void
    // {
    //     $response = $this->executeFrontendSubRequest(
    //         (new \TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest('http://localhost/'))
    //             ->withHeader('REMOTE_ADDR', '8.8.8.8')
    //     );

    //     $headers = $response->getHeaders();

    //     self::assertArrayHasKey('Set-Cookie', $headers);

    //     $cookieHeader = implode(';', $headers['Set-Cookie']);
    //     self::assertStringContainsString('fe_user_country=', $cookieHeader);
    // }

    /**
     * @test
     */
    // public function multipleCountryCodesAreHandledCorrectly(): void
    // {
    //     $testCases = [
    //         'DE' => true,  // Should see content
    //         'FR' => true,  // Should see content
    //         'IT' => true,  // Should see content
    //         'US' => false, // Should not see content
    //         'GB' => false, // Should not see content
    //     ];

    //     foreach ($testCases as $country => $shouldSeeContent) {
    //         $_COOKIE['fe_user_country'] = $country;

    //         $response = $this->executeFrontendSubRequest(
    //             (new \TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest('http://localhost/'))
    //         );

    //         $content = (string) $response->getBody();

    //         if ($shouldSeeContent) {
    //             self::assertStringContainsString(
    //                 'DACH Content',
    //                 $content,
    //                 "Content should be visible for $country"
    //             );
    //         } else {
    //             self::assertStringNotContainsString(
    //                 'DACH Content',
    //                 $content,
    //                 "Content should be hidden for $country"
    //             );
    //         }
    //     }

    //     unset($_COOKIE['fe_user_country']);
    // }

    // /**
    //  * @test
    //  */
    // public function geoLocationServiceIsUsedInMiddleware(): void
    // {
    //     $ipAddress = '93.184.216.34';

    //     $response = $this->executeFrontendSubRequest(
    //         (new \TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest('http://localhost/'))
    //             ->withServerParams(['REMOTE_ADDR' => $ipAddress])
    //     );

    //     self::assertEquals(200, $response->getStatusCode());

    //     // Cookie should be set based on geo-location
    //     $headers = $response->getHeaders();
    //     self::assertArrayHasKey('Set-Cookie', $headers);
    // }

    // /**
    //  * @test
    //  */
    // public function cacheStoresGeoLocationResults(): void
    // {

    //     $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
    //     $cache = $cacheManager->getCache('hidebycountries');

    //     $ipAddress = '8.8.8.8';
    //     $cacheIdentifier = sha1('geoip_' . $ipAddress);
    //     ;

    //     // Clear cache first
    //     $cache->flush();

    //     // Simulate cache entry
    //     $cache->set($cacheIdentifier, 'US', [], 3600);

    //     $cachedValue = $cache->get($cacheIdentifier);
    //     self::assertEquals('US', $cachedValue);

    // }

    // /**
    //  * @test
    //  */
    // public function developmentModeUsesTestIpAddress(): void
    // {
    //     $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['hidebycountries']['developmentMode'] = '1';
    //     $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['hidebycountries']['publicIpAddressForTesting'] = '8.8.8.8';

    //     $response = $this->executeFrontendSubRequest(
    //         (new \TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest('http://localhost/'))
    //             ->withServerParams(['REMOTE_ADDR' => '127.0.0.1'])
    //     );

    //     self::assertEquals(200, $response->getStatusCode());

    //     unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['hidebycountries']);
    // }
}
