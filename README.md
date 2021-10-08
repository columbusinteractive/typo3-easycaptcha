# TYPO3 - Easy Captcha (image based)
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
The captcha can be styled if necessary. In most cases however, the default styling should be sufficient. The following classes are available:

* easycaptcha (container)
  * easycaptcha--initialized (initialized captcha)
* easycaptcha__challenge (wrapper for image and buttons)
* easycaptcha__image-wrapper (wrapper for image)
* easycaptcha__image (image)
* easycaptcha__actions (wrapper for buttons)
* easycaptcha__action-tts (tts button)
* easycaptcha__action-reload (reload button)
* easycaptcha__input-wrapper (wrapper for input)

## JS API
Tha JavaScript API can be accessed using `window.easycaptcha`.

Available functions:
```ts
easycaptcha.init(id: string|HTMLElement): void;
easycaptcha.initAll(): void;
easycaptcha.reload(id: string|HTMLElement): void;
easycaptcha.tts(id: string|HTMLElement): Promise;
easycaptcha.get(id: string|HTMLElement): HTMLElement;
easycaptcha.getAll(): HTMLCollection;
```

## Demo
We're using the captcha on our corporate website  for the contact form.  
https://www.columbus-interactive.de/kontakt

## License
This TYPO3 extension is open-sourced software licensed under the [MIT-Licence](https://github.com/columbusinteractive/typo3-easycaptcha/blob/master/LICENSE)
