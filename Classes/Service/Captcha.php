<?php
declare(strict_types=1);

namespace ColumbusInteractive\EasyCaptcha\Service;

use RuntimeException;
use Laminas\Captcha\Image;
use Laminas\Session\Config\SessionConfig;
use Laminas\Session\Container;
use Laminas\Session\SessionManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use ColumbusInteractive\EasyCaptcha\Exceptions\ElemenIdentifierNotFoundInForm;
use ColumbusInteractive\EasyCaptcha\Exceptions\MissingFormElement;
use ColumbusInteractive\EasyCaptcha\Exceptions\UnableToLoadFormConfiguration;

final class Captcha
{
    private const PUBLIC_CAPTCHA_DIRECTORY = 'captcha';

    /** @var Image */
    private $captcha;

    public function __construct()
    {
        $this->initialize();
    }

    private function initialize(): void
    {
        $extensionDir = dirname(__DIR__, 2);
        $this->captcha = new Image();
        $this->captcha->setFont($extensionDir . '/Resources/Private/Fonts/arial.ttf')
            ->setImgDir(self::getPublicCaptchaPath())
            ->setWidth(300)
            ->setHeight(100)
            ->setFontSize(25)
            ->setWordlen(4)
            ->setLineNoiseLevel(20)
            ->setDotNoiseLevel(100);

        $this->captcha->setSession((new Container('captcha', (new SessionManager(
            (new SessionConfig())
                ->setOptions([
                    'name' => 'captcha',
                ])
        )))));
    }

    public function getCaptcha(bool $loadProperties = false): Image
    {
        if ($loadProperties) {
            $captchaProperties = $this->getCaptchaProperties();

            $this->captcha->setWidth($captchaProperties['width'])
                ->setHeight($captchaProperties['height'])
                ->setFontSize($captchaProperties['fontSize'])
                ->setWordlen($captchaProperties['wordLength'])
                ->setLineNoiseLevel($captchaProperties['lineNoiseLevel'])
                ->setDotNoiseLevel($captchaProperties['dotNoiseLevel']);
        }

        return $this->captcha;
    }

    public static function getPublicCaptchaPath(): string
    {
        $path = Environment::getPublicPath() . '/typo3temp/' . self::PUBLIC_CAPTCHA_DIRECTORY;

        if (!is_dir($path)) {
            if (!mkdir($path) && !is_dir($path)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }

        return $path;
    }

    private function getCaptchaProperties(): array
    {
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
            ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class));

        $statement = $queryBuilder
            ->select('pi_flexform')
            ->from('tt_content');

        $result = $statement->andWhere(
            $queryBuilder->expr()->and(
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter($sys_language_uid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter((int)$_GET['pid'], \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter('form_formframework', \PDO::PARAM_STR)
                )
            )
        )->executeQuery()->fetchOne();

        if (empty($result)) {
            $result = $statement->orWhere(
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq(
                        'sys_language_uid',
                        $queryBuilder->createNamedParameter(-1, \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'pid',
                        $queryBuilder->createNamedParameter((int)$_GET['pid'], \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'CType',
                        $queryBuilder->createNamedParameter('form_formframework', \PDO::PARAM_STR)
                    )
                )
            )->executeQuery()->fetchOne();
        }

        if ($result === false) {
            throw MissingFormElement::make('Unable to find a form element for the given pid: ' . (int)$_GET['pid']);
        }

        $flexFormArray = GeneralUtility::xml2array($result);

        $filePath = GeneralUtility::getFileAbsFileName($flexFormArray['data']['sDEF']['lDEF']['settings.persistenceIdentifier']['vDEF']);

        if (!file_exists($filePath) ) {
            throw UnableToLoadFormConfiguration::make('Unable to load: ' . $flexFormArray['data']['sDEF']['lDEF']['settings.persistenceIdentifier']['vDEF']);
        }

        $yamlLoader = new YamlFileLoader();
        $formConfiguration = $yamlLoader->load($filePath);
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

        return $captchaProperties;
    }
}
