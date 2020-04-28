<?php
declare(strict_types=1);

namespace ColumbusInteractive\EasyCaptcha\Validation\Validator;

use ColumbusInteractive\EasyCaptcha\Service\Captcha;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

final class EasyCaptcha extends AbstractValidator
{
    protected function isValid($value): void
    {
        $captchaService = Captcha::getInstance();
        if ($captchaService->getCaptcha()->getWord() !== $value) {
            $this->addError($this->translateErrorMessage(
                'captchaError',
                'easycaptcha'
            ), 1587727590);
        }
    }
}
