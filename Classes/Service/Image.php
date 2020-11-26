<?php
declare(strict_types=1);

use ColumbusInteractive\EasyCaptcha\Exceptions\ElemenIdentifierNotFoundInForm;
use ColumbusInteractive\EasyCaptcha\Exceptions\MissingFormElement;
use ColumbusInteractive\EasyCaptcha\Exceptions\UnableToLoadFormConfiguration;
use ColumbusInteractive\EasyCaptcha\Service\Captcha;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendWorkspaceRestriction;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

(static function () {

    // Default vendor dir
    if (file_exists(dirname(__DIR__, 6) . '/vendor/autoload.php')) {
        // Composer mode
        $classLoader = require dirname(__DIR__, 6) . '/vendor/autoload.php';
        SystemEnvironmentBuilder::run(5, SystemEnvironmentBuilder::REQUESTTYPE_FE);
        Bootstrap::init($classLoader);

    // Custom vendor dir
    } else if (isset($_SERVER['DOCUMENT_ROOT']) &&
        file_exists(dirname($_SERVER['DOCUMENT_ROOT'], 1) . '/composer.json')) {
        $composerConfig = json_decode(file_get_contents(
            dirname($_SERVER['DOCUMENT_ROOT'], 1) . '/composer.json'
        ), true, 512, JSON_THROW_ON_ERROR);

        if (isset($composerConfig['config']['vendor-dir'])) {
            $classLoader = require dirname(__DIR__, 6) . '/' . $composerConfig['config']['vendor-dir'] . '/autoload.php';
            SystemEnvironmentBuilder::run(5, SystemEnvironmentBuilder::REQUESTTYPE_FE);
            Bootstrap::init($classLoader);
        }
    }

    // If some of the above autoload checks were successful, we should have a TYPO3_COMPOSER_MODE env.
    if (TYPO3_COMPOSER_MODE !== true) {
        throw new RuntimeException('Unable to find composer vendor dir! Autoload failed.');
    }


    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
        ->getQueryBuilderForTable('tt_content');

    $queryBuilder
        ->getRestrictions()
        ->removeAll()
        ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
        ->add(GeneralUtility::makeInstance(FrontendWorkspaceRestriction::class));

    $statement = $queryBuilder
        ->select('pi_flexform')
        ->from('tt_content')
        ->andWhere($queryBuilder->expr()->andX(
            $queryBuilder->expr()->eq(
                'pid',
                $queryBuilder->createNamedParameter((int)$_GET['pid'], PDO::PARAM_INT)
            ),
            $queryBuilder->expr()->eq(
                'CType',
                $queryBuilder->createNamedParameter('form_formframework', PDO::PARAM_STR)
            )
        ))->execute();

    $result = $statement->fetch();

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
    $fileWithoutSuffix = $captcha::getPublicCaptchaPath() . '/' . $captcha->getCaptcha()->generate();

    // Write JS File for text to speech
    file_put_contents($captcha::getPublicCaptchaPath() . '/tts.mjs', 'export function tts() { return "' . $captcha->getCaptcha()->getWord() . '" }');
    header('Content-Type: image/png');
    $im = imagecreatefrompng($fileWithoutSuffix . $captcha->getCaptcha()->getSuffix());
    imagepng($im);
    imagedestroy($im);
})();
