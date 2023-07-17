<?php
declare(strict_types=1);

defined('TYPO3') || die('Access denied.');

// Add Default TS to Include static (from extensions)
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'easycaptcha',
    'Configuration/TypoScript/',
    'Easy Captcha'
);
