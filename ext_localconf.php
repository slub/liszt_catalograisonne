<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Log\LogLevel;

defined('TYPO3') or die();

$extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('liszt_catalograisonne');

$logLevel = match((int)($extConf['logLevel'] ?? 3)) {
    0 => LogLevel::INFO,
    1 => LogLevel::NOTICE,
    2 => LogLevel::WARNING,
    default => LogLevel::ERROR
};
