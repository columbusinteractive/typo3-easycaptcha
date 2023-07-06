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
        $this->templateVariableContainer->add('currentPid', $tsfe->getRequestedId());
    }
}
