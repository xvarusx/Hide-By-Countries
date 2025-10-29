<?php
use Oussema\HideByCountries\Hooks\GetCountriesTca;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$fields = [
    'tx_hidebycountries' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:hidebycountries/Resources/Private/Language/locallang.xlf:tt_content.tx_hidebycountries',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'itemsProcFunc' => GetCountriesTca::class . '->loadCountries',
            'size' => 10,
            'maxitems' => 9999,
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('tt_content', $fields);
ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;LLL:EXT:hidebycountries/Resources/Private/Language/locallang.xlf:tabs.visibility,tx_hidebycountries'
);