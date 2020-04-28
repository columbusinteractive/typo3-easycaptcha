<?php
declare(strict_types=1);

namespace ColumbusInteractive\EasyCaptcha\ViewHelpers;

use ColumbusInteractive\EasyCaptcha\Domain\Model\FormElements\EasyCaptcha;
use ColumbusInteractive\EasyCaptcha\Service\Captcha;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

final class RenderViewHelper extends AbstractFormFieldViewHelper
{
    public function render(): void
    {
        $captchaService = Captcha::getInstance();

        /** @var EasyCaptcha $captchaElement */
        $captchaElement = $this->templateVariableContainer->getByPath('element');
        $captchaService->setCaptchaProperties($captchaElement->getProperties());

        $this->templateVariableContainer->add('captchaImage',
            PathUtility::getAbsoluteWebPath($captchaService->getDynamicImagePublicPath()));
        $this->templateVariableContainer->add('ttsHelperPath',
            PathUtility::getAbsoluteWebPath($captchaService->getTtsHelperPublicPath()));
        $this->templateVariableContainer->add('captchaImageAltAttribute',
            $captchaService->getCaptcha()->getImgAlt());
        $this->templateVariableContainer->add('publicCaptchaPath',
            PathUtility::getAbsoluteWebPath($captchaService::getPublicCaptchaPath()));
    }
}
