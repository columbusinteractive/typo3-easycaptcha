<?php
declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') || die('Access denied.');

// Add Default TS to Include static (from extensions)
ExtensionManagementUtility::addStaticFile(
    'easycaptcha',
    'Configuration/TypoScript/',
    'Easy Captcha'
);
