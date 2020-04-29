<?php
declare(strict_types=1);

namespace ColumbusInteractive\EasyCaptcha\ViewHelpers;

use ColumbusInteractive\EasyCaptcha\Service\Captcha;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

final class RenderViewHelper extends AbstractFormFieldViewHelper
{
    public function render(): void
    {
        /** @var TypoScriptFrontendController $tsfe */
        $tsfe = $GLOBALS['TSFE'];
        $captchaService = Captcha::getInstance();

        $this->templateVariableContainer->add('captchaImage',
            PathUtility::getAbsoluteWebPath($captchaService->getDynamicImagePublicPath()));
        $this->templateVariableContainer->add('ttsHelperPath',
            PathUtility::getAbsoluteWebPath($captchaService->getTtsHelperPublicPath()));
        $this->templateVariableContainer->add('captchaImageAltAttribute',
            $captchaService->getCaptcha()->getImgAlt());
        $this->templateVariableContainer->add('publicCaptchaPath',
            PathUtility::getAbsoluteWebPath($captchaService::getPublicCaptchaPath()));
        $this->templateVariableContainer->add('currentPid', $tsfe->getRequestedId());
    }
}
