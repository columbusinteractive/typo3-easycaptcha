<?php
declare(strict_types=1);

namespace ColumbusInteractive\EasyCaptcha\Service;

use Laminas\Captcha\Image;
use Laminas\Session\Config\SessionConfig;
use Laminas\Session\Container;
use Laminas\Session\SessionManager;
use RuntimeException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

final class Captcha
{
    private const PUBLIC_CAPTCHA_DIRECTORY = 'captcha';
    public const SESSION_KEY = 'captchaProperties';

    /** @var Image */
    private $captcha;

    public static function getInstance(): Captcha
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(
            ObjectManager::class
        );
        /** @var self $instance */
        $instance = $objectManager->get(self::class);
        return $instance;
    }

    public function injectObjectManager(ObjectManagerInterface $objectManager): void
    {
        $this->objectManager = $objectManager;
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

    public function getCaptcha(): Image
    {
        return $this->captcha;
    }

    public function getDynamicImagePublicPath(): string
    {
        return Environment::getPublicPath() . '/typo3conf/ext/easycaptcha/Classes/Service/Image.php';
    }

    public function getTtsHelperPublicPath(): string
    {
        return Environment::getPublicPath() . '/typo3conf/ext/easycaptcha/Classes/Service/TtsHelper.php';
    }
}
