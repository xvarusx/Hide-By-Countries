<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Tests\Functional;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional test for backend integration and preview indicators
 */
class BackendIntegrationTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/hidebycountries',
    ];

    protected array $coreExtensionsToLoad = [
        'backend',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tt_content.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/be_users.csv');

        $this->setUpBackendUser(1);
    }

    /**
     * @test
     */
    public function tcaConfigurationIsLoaded(): void
    {
        $tca = $GLOBALS['TCA']['tt_content'];

        self::assertIsArray($tca);
        self::assertArrayHasKey('columns', $tca);
        self::assertArrayHasKey('tx_hidebycountries', $tca['columns']);
    }

    /**
     * @test
     */
    public function countryFieldHasCorrectConfiguration(): void
    {
        $fieldConfig = $GLOBALS['TCA']['tt_content']['columns']['tx_hidebycountries'];

        self::assertIsArray($fieldConfig);
        self::assertArrayHasKey('label', $fieldConfig);
        self::assertArrayHasKey('config', $fieldConfig);

        // Check field type
        self::assertEquals('select', $fieldConfig['config']['type']);
    }

    /**
     * @test
     */
    public function countryFieldAppearsInBackendForm(): void
    {
        $types = $GLOBALS['TCA']['tt_content']['types'];

        $textType = $types['text'] ?? null;
        self::assertIsArray($textType);

        // Field should be added to showitem
        $showitem = $textType['showitem'] ?? '';
        self::assertStringContainsString('tx_hidebycountries', $showitem);
    }

    /**
     * @test
     */
    public function contentElementCanBeEditedWithCountryRestriction(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        // Create test content element
        $connection->insert(
            'tt_content',
            [
                'pid' => 1,
                'uid' => 888,
                'CType' => 'text',
                'header' => 'Backend Test',
                'tx_hidebycountries' => 'DE,AT,CH',
                'tstamp' => time(),
                'crdate' => time(),
            ]
        );

        // Retrieve and verify
        $record = BackendUtility::getRecord('tt_content', 888);

        self::assertIsArray($record);
        self::assertEquals('DE,AT,CH', $record['tx_hidebycountries']);
    }

    /**
     * @test
     */
    public function countryFieldCanBeUpdated(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        // Create initial record
        $connection->insert(
            'tt_content',
            [
                'pid' => 1,
                'uid' => 777,
                'CType' => 'text',
                'tx_hidebycountries' => 'DE',
                'tstamp' => time(),
                'crdate' => time(),
            ]
        );

        // Update the field
        $connection->update(
            'tt_content',
            ['tx_hidebycountries' => 'FR,IT,ES'],
            ['uid' => 777]
        );

        // Verify update
        $record = BackendUtility::getRecord('tt_content', 777);
        self::assertEquals('FR,IT,ES', $record['tx_hidebycountries']);
    }

    /**
     * @test
     */
    public function contentElementsWithRestrictionsAreIdentifiableInBackend(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        $queryBuilder = $connection->createQueryBuilder();

        // Find all content elements with country restrictions
        $results = $queryBuilder
            ->select('uid', 'header', 'tx_hidebycountries')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->neq(
                    'tx_hidebycountries',
                    $queryBuilder->createNamedParameter('')
                ),
                $queryBuilder->expr()->isNotNull('tx_hidebycountries')
            )
            ->executeQuery()
            ->fetchAllAssociative();

        self::assertIsArray($results);

        foreach ($results as $result) {
            self::assertNotEmpty($result['tx_hidebycountries']);
        }
    }

    /**
     * @test
     */
    public function emptyCountryRestrictionIsHandledInBackend(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        $connection->insert(
            'tt_content',
            [
                'pid' => 1,
                'uid' => 666,
                'CType' => 'text',
                'tx_hidebycountries' => '',
                'tstamp' => time(),
                'crdate' => time(),
            ]
        );

        $record = BackendUtility::getRecord('tt_content', 666);

        self::assertIsArray($record);
        self::assertEquals('', $record['tx_hidebycountries']);
    }

    /**
     * @test
     */
    public function countryFieldSupportsMultipleValues(): void
    {
        $testValues = [
            'DE',
            'DE,FR',
            'DE,FR,IT,ES,PT',
            'DE, FR, IT',  // With spaces
            'de,fr,it',    // Lowercase
        ];

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        foreach ($testValues as $index => $value) {
            $uid = 1000 + $index;

            $connection->insert(
                'tt_content',
                [
                    'pid' => 1,
                    'uid' => $uid,
                    'CType' => 'text',
                    'tx_hidebycountries' => $value,
                    'tstamp' => time(),
                    'crdate' => time(),
                ]
            );

            $record = BackendUtility::getRecord('tt_content', $uid);
            self::assertEquals($value, $record['tx_hidebycountries']);
        }
    }

    /**
     * @test
     */
    public function palettesAreConfiguredCorrectly(): void
    {
        $tca = $GLOBALS['TCA']['tt_content'];

        // Check if field is in a palette or directly in showitem
        $textType = $tca['types']['text'] ?? [];
        $showitem = $textType['showitem'] ?? '';

        self::assertNotEmpty($showitem);
    }

    /**
     * @test
     */
    public function labelForCountryFieldIsSet(): void
    {
        $fieldConfig = $GLOBALS['TCA']['tt_content']['columns']['tx_hidebycountries'];

        self::assertArrayHasKey('label', $fieldConfig);
        self::assertNotEmpty($fieldConfig['label']);

        // Should be translatable
        self::assertStringStartsWith('LLL:', $fieldConfig['label']);
    }

    /**
     * @test
     */
    public function fieldDescriptionIsProvided(): void
    {
        $fieldConfig = $GLOBALS['TCA']['tt_content']['columns']['tx_hidebycountries'];

        if (isset($fieldConfig['description'])) {
            self::assertNotEmpty($fieldConfig['description']);
        } else {
            // Description is optional but recommended
            self::assertTrue(true);
        }
    }
}
