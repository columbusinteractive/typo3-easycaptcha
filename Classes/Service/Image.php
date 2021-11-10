<?php
declare(strict_types=1);

use ColumbusInteractive\EasyCaptcha\Exceptions\ElemenIdentifierNotFoundInForm;
use ColumbusInteractive\EasyCaptcha\Exceptions\MissingFormElement;
use ColumbusInteractive\EasyCaptcha\Exceptions\UnableToLoadFormConfiguration;
use ColumbusInteractive\EasyCaptcha\Service\Captcha;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendWorkspaceRestriction;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

(static function () {
    require '../../composer_autoload.php';

    $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
    assert($context instanceof \TYPO3\CMS\Core\Context\Context);
    $languageAspect = $context->getAspect('language');
    assert($languageAspect instanceof \TYPO3\CMS\Core\Context\LanguageAspect);
    $sys_language_uid = $languageAspect->getId();

    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
        ->getQueryBuilderForTable('tt_content');

    $queryBuilder
        ->getRestrictions()
        ->removeAll()
        ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
        ->add(GeneralUtility::makeInstance(FrontendWorkspaceRestriction::class));

    $statement = $queryBuilder
        ->select('pi_flexform')
        ->from('tt_content');

    $result = $statement->andWhere(
        $queryBuilder->expr()->andX(
            $queryBuilder->expr()->eq(
                'sys_language_uid',
                $queryBuilder->createNamedParameter($sys_language_uid, PDO::PARAM_INT)
            ),
            $queryBuilder->expr()->eq(
                'pid',
                $queryBuilder->createNamedParameter((int)$_GET['pid'], PDO::PARAM_INT)
            ),
            $queryBuilder->expr()->eq(
                'CType',
                $queryBuilder->createNamedParameter('form_formframework', PDO::PARAM_STR)
            )
        )
    )->execute()->fetch();

    if (empty($result)) {
        $result = $statement->orWhere(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(-1, PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter((int)$_GET['pid'], PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter('form_formframework', PDO::PARAM_STR)
                )
            )
        )->execute()->fetch();
    }

    if ($result === false) {
        throw MissingFormElement::make('Unable to find a form element for the given pid: ' . (int)$_GET['pid']);
    }

    $flexFormArray = GeneralUtility::xml2array($result['pi_flexform']);
    $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);

    /** @var \TYPO3\CMS\Core\Resource\File $file */
    $file = $resourceFactory->retrieveFileOrFolderObject(
        $flexFormArray['data']['sDEF']['lDEF']['settings.persistenceIdentifier']['vDEF']
    );

    if (!$file instanceof \TYPO3\CMS\Core\Resource\File) {
        throw UnableToLoadFormConfiguration::make('Unable to load: ' . $flexFormArray['data']['sDEF']['lDEF']['settings.persistenceIdentifier']['vDEF']);
    }

    $yamlLoader = new YamlFileLoader();
    $formConfiguration = $yamlLoader->load($file->getPublicUrl());
    $captchaProperties = null;

    foreach ($formConfiguration['renderables'] as $renderable) {
        if (isset($renderable['renderables'])) {
            foreach ($renderable['renderables'] as $element) {
                if ($element['identifier'] === $_GET['identifier']) {
                    $captchaProperties = $element['properties'];
                }
            }
        }
    }

    if ($captchaProperties === null) {
        throw ElemenIdentifierNotFoundInForm::make('Unable to find a form element with the given identifier: ' . htmlspecialchars($_GET['identifier']));
    }

    $captcha = Captcha::getInstance();
    $captcha->getCaptcha()
        ->setWidth($captchaProperties['width'])
        ->setHeight($captchaProperties['height'])
        ->setLineNoiseLevel($captchaProperties['lineNoiseLevel'])
        ->setDotNoiseLevel($captchaProperties['dotNoiseLevel'])
        ->setWordlen($captchaProperties['wordLength'])
        ->setFontSize($captchaProperties['fontSize']);

    // Generate captcha
    $path = Captcha::getPublicCaptchaPath() . '/' . $captcha->getCaptcha()->generate() . $captcha->getCaptcha()->getSuffix();

    // Emit image
    header('Content-Type: image/png');
    $im = imagecreatefrompng($path);
    imagepng($im);
    imagedestroy($im);
})();
