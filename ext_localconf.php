<?php

(static function () {

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:form/Resources/Private/Language/Database.xlf'][] =
        'EXT:easycaptcha/Resources/Private/Language/Backend.xlf';

    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        't3-form-icon-easycaptcha',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:easycaptcha/Resources/Public/Icons/Extension.svg']
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('
module.tx_form.settings.yamlConfigurations {
    1982 = EXT:easycaptcha/Configuration/Yaml/BaseSetup.yaml
    1983 = EXT:easycaptcha/Configuration/Yaml/FormEditorSetup.yaml
}');

    $isComposerMode = defined('TYPO3_COMPOSER_MODE') && TYPO3_COMPOSER_MODE;
    if(!$isComposerMode) {
        // we load the autoloader for our libraries
        $dir = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('easycaptcha');
        require $dir . '/Resources/Private/Php/Composer/vendor/autoload.php';
    }
})();
