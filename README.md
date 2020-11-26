# TYPO3 - Easy Captcha (image based)
[![Latest Stable Version](https://poser.pugx.org/columbusinteractive/typo3-easycaptcha/v/stable)](https://packagist.org/packages/columbusinteractive/typo3-easycaptcha)
[![Monthly Downloads](https://poser.pugx.org/columbusinteractive/typo3-easycaptcha/d/monthly)](https://packagist.org/packages/columbusinteractive/typo3-easycaptcha)
[![Total Downloads](https://poser.pugx.org/columbusinteractive/typo3-easycaptcha/downloads)](https://packagist.org/packages/columbusinteractive/typo3-easycaptcha)
[![License](https://poser.pugx.org/columbusinteractive/typo3-easycaptcha/license.svg)](https://packagist.org/packages/columbusinteractive/typo3-easycaptcha)
 
An easy to use extension which enables you to use captchas in the TYPO3 form extension

## Installation via Composer
```shell
composer require columbusinteractive/typo3-easycaptcha
```

## How it works
Under the hood this extension makes use of the fantastic ``laminas/laminas-captcha`` library. This extension
is using a separated session container to make things easier. A separate session cookie named ``captcha`` is set 
and only valid for the current browser session. Keep this in mind for your cookie consent or privacy settings!
Generated captcha images are automatically gargabe collected from time to time, so no worries.

## Usage
After you've installed the extension, you will see a new field called "Easy captcha" in your TYPO3 forms editor. 
Simply add the field and you are good to go. Don't forget to add the static typoscript file in your template!

## Customization
The following options are available in the TYPO3 form editor:
* Width of the generated image
* Height of the generated image
* Dot noise level
* Line noise level
* Font size
* Word length
* Option to enable or disable TTS (text-to-speech). This feature uses the ``SpeechSynthesis API`` 
(https://developer.mozilla.org/en-US/docs/Web/API/SpeechSynthesis)

## Styling
The captcha field can be styled with the following CSS classes:
* easycaptcha (Container for the image)
* captcha-actions (Container for the action buttons e.g. reload or play)

By default, bootstrap classes are also added for the form field. If you don't use bootstrap, simply ignore them.

## Demo
We're using the captcha on our corporate website  for the contact form.  
https://www.columbus-interactive.de/kontakt

## License
This TYPO3 extension is open-sourced software licensed under the [MIT-Licence](https://github.com/columbusinteractive/typo3-easycaptcha/blob/master/LICENSE)
