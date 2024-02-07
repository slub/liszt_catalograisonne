<?php

declare(strict_types=1);

use Slub\LisztCatalograisonne\Controller\WorkController;
use Slub\LisztCatalograisonne\Services\MermeidXmlRpcService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin(
    'LisztCatalograisonne',
    'WorkListing',
    [ WorkController::class => 'index' ],
    [ WorkController::class => 'index' ]
);

ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:liszt_catalograisonne/Configuration/TsConfig/Page/Mod/Wizards/Listing.tsconfig">'
);

ExtensionManagementUtility::addService(
    'LisztCatalograisonne',
    'mermeid',
    'tx_lisztcatalograisonne_mermeid',
    [
        'title' => 'MerMEId',
        'description' => 'Retrieve and store documents in a MerMEId instance',
        'subtype' => '',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => MermeidXmlRpcService::class
    ]
);
