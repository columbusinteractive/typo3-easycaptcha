<?php

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

(static function () {

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:form/Resources/Private/Language/Database.xlf'][] =
        'EXT:easycaptcha/Resources/Private/Language/Backend.xlf';

    /** @var IconRegistry $iconRegistry */
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    $iconRegistry->registerIcon(
        't3-form-icon-easycaptcha',
        SvgIconProvider::class,
        ['source' => 'EXT:easycaptcha/Resources/Public/Icons/Extension.svg']
    );

    ExtensionManagementUtility::addTypoScriptSetup('
module.tx_form.settings.yamlConfigurations {
    1982 = EXT:easycaptcha/Configuration/Yaml/BaseSetup.yaml
    1983 = EXT:easycaptcha/Configuration/Yaml/FormEditorSetup.yaml
}');

    $isComposerMode = defined('TYPO3_COMPOSER_MODE') && TYPO3_COMPOSER_MODE;
    if(!$isComposerMode) {
        // we load the autoloader for our libraries
        $dir = ExtensionManagementUtility::extPath('easycaptcha');
        require $dir . '/Resources/Private/Php/Composer/vendor/autoload.php';
    }
})();
