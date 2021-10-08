<?php
declare(strict_types=1);

use ColumbusInteractive\EasyCaptcha\Service\Captcha;

(static function () {
    require '../../composer_autoload.php';

    $captcha = Captcha::getInstance();

    $word = $captcha->getCaptcha()->getWord();

    header('Content-Type: application/json');
    echo json_encode([
        'word' => $word !== null
            ? implode(' ', str_split($word))
            : null,
    ]);
})();
