<?php
declare(strict_types=1);

use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;

// Default vendor dir
if (file_exists(dirname(__DIR__, 4) . '/vendor/autoload.php')) {
    // Composer mode
    $classLoader = require dirname(__DIR__, 4) . '/vendor/autoload.php';
    SystemEnvironmentBuilder::run(5, SystemEnvironmentBuilder::REQUESTTYPE_FE);
    Bootstrap::init($classLoader);

// Custom vendor dir
} else if (isset($_SERVER['DOCUMENT_ROOT']) &&
    file_exists(dirname($_SERVER['DOCUMENT_ROOT'], 1) . '/composer.json')) {
    $composerConfig = json_decode(file_get_contents(
        dirname($_SERVER['DOCUMENT_ROOT'], 1) . '/composer.json'
    ), true);

    if (isset($composerConfig['config']['vendor-dir'])) {
        $classLoader = require dirname(__DIR__, 4) . '/' . $composerConfig['config']['vendor-dir'] . '/autoload.php';
        SystemEnvironmentBuilder::run(5, SystemEnvironmentBuilder::REQUESTTYPE_FE);
        Bootstrap::init($classLoader);
    }
}

// If some of the above autoload checks were successful, we should have a TYPO3_COMPOSER_MODE env.
if (TYPO3_COMPOSER_MODE !== true) {
    throw new RuntimeException('Unable to find composer vendor dir! Autoload failed.');
}
