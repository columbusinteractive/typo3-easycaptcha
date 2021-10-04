<?php
declare(strict_types=1);

use ColumbusInteractive\EasyCaptcha\Service\Captcha;

(static function () {
    require '../../composer_autoload.php';

    $captcha = Captcha::getInstance();

    header('Content-Type: application/json');
    echo json_encode([
        'word' => implode(' ', str_split($captcha->getCaptcha()->getWord()))
    ]);
})();
