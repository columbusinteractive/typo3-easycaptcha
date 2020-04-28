<?php
declare(strict_types=1);

use ColumbusInteractive\EasyCaptcha\Service\Captcha;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;

require_once dirname(__DIR__, 4) . '/vendor/autoload.php';

call_user_func(static function () {
    $classLoader = require dirname(__DIR__, 4) . '/vendor/autoload.php';
    SystemEnvironmentBuilder::run(5, SystemEnvironmentBuilder::REQUESTTYPE_FE);
    Bootstrap::init($classLoader);


    $captcha = Captcha::getInstance();
    $properties = $captcha->getCaptchaProperties();
    $captcha->getCaptcha()
        ->setWidth($properties['width'])
        ->setHeight($properties['height'])
        ->setLineNoiseLevel($properties['lineNoiseLevel'])
        ->setDotNoiseLevel($properties['dotNoiseLevel'])
        ->setWordlen($properties['wordLength'])
        ->setFontSize($properties['fontSize']);

    // Generate captcha
    $fileWithoutSuffix = $captcha::getPublicCaptchaPath() . '/' . $captcha->getCaptcha()->generate();

    // Write JS File for text to speech
    file_put_contents($captcha::getPublicCaptchaPath() . '/tts.mjs', 'export function tts() { return "' . $captcha->getCaptcha()->getWord() . '" }');
    header('Content-Type: image/png');
    $im = imagecreatefrompng($fileWithoutSuffix . $captcha->getCaptcha()->getSuffix());
    imagepng($im);
    imagedestroy($im);
});
