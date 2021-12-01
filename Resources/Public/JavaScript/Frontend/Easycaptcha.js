(function () {
    /**
     * @param {string|HTMLElement} id
     * @return {HTMLElement}
     * @throws Error
     */
    function get(id) {
        if (! id) {
            throw new Error('Parameter required to find captcha');
        }

        /** @type {HTMLElement} */
        var captcha;
        if (typeof id === 'string') {
            captcha = document.querySelector('#' + id);
            if (! captcha) {
                throw new Error('Unable to find captcha with id "' + id + '"');
            }
        } else {
            captcha = id;
        }

        if (! captcha.classList.contains(easycaptcha.classnames.container)) {
            throw new Error('Invalid captcha node');
        }

        return captcha;
    }

    /**
     * @return {HTMLCollectionOf<Element>}
     */
    function getAll() {
        return document.getElementsByClassName(easycaptcha.classnames.container);
    }

    /**
     * @param {string|HTMLElement} captcha
     * @throws Error
     */
    function reload(captcha) {
        if (! captcha) {
            throw new Error('Parameter required to reload captcha');
        }

        if (easycaptcha.logging) {
            console.debug('Reloading captcha...');
        }

        captcha = easycaptcha.get(captcha);

        var image = captcha.querySelector('img.' + easycaptcha.classnames.image);
        if (! image) {
            throw new Error('Unable to find image for captcha with id "' + captcha.id + '"');
        }

        var url = image.src;

        // Strip existing time & random
        var timeIndex = url.indexOf('&t=');
        if (timeIndex !== -1) {
            url = url.substring(0, timeIndex);
        }

        url += '&t=' + new Date().getTime() + '&r=' + Math.round(Math.random() * 1000000000);

        image.src = url;
    }

    /**
     * @return {boolean}
     */
    function checkTtsSupport() {
        return window.speechSynthesis !== undefined
            && window.SpeechSynthesisUtterance !== undefined
            && window.fetch !== undefined;
    }

    /**
     * @param {string|HTMLElement} captcha
     * @return {Promise<null>}
     * @throws Error
     */
    function tts(captcha) {
        if (! captcha) {
            throw new Error('Parameter required to play tts');
        }

        if (! easycaptcha.checkTtsSupport()) {
            throw new Error('Tts not supported');
        }

        if (easycaptcha.logging) {
            console.debug('Playing tts...');
        }

        captcha = easycaptcha.get(captcha);

        var button = captcha.querySelector('.' + easycaptcha.classnames.ttsButton);
        if (! button) {
            throw new Error('Unable to find tts button for captcha with id "' + captcha.id + '"');
        }

        var url = button.dataset.ttsUrl;
        if (! url) {
            throw new Error('Unable to find tts url for captcha with id "' + captcha.id + '"');
        }

        var promise = fetch(url)
            .then(function (response) {
                if (! response.ok) {
                    throw new Error('HTTP error [' + response.status + '] for captcha tts request');
                }

                return response.json();
            })
            .then(function (data) {
                var utterance = new SpeechSynthesisUtterance(data.word);
                utterance.rate = 0.8;
                utterance.pitch = 1;
                speechSynthesis.speak(utterance);

                return null;
            });

        promise.catch(function (e) {
            if (easycaptcha.logging) {
                console.error(e);
            }
        });

        return promise;
    }

    /**
     * @param {string|HTMLElement} captcha
     */
    function init(captcha) {
        if (! captcha) {
            throw new Error('Parameter required to initialize');
        }

        if (easycaptcha.logging) {
            console.debug('Initializing captcha...');
        }

        captcha = easycaptcha.get(captcha);

        if (captcha.classList.contains(easycaptcha.classnames.initializedState)) {
            return;
        }

        // Reload button
        var reloadButton = captcha.querySelector('.' + easycaptcha.classnames.reloadButton);
        if (reloadButton) {
            reloadButton.addEventListener('click', function () {
                easycaptcha.reload(captcha);
            });
            reloadButton.style.display = null;
        }

        // Tts button
        if (easycaptcha.checkTtsSupport()) {
            var ttsButton = captcha.querySelector('.' + easycaptcha.classnames.ttsButton);
            if (ttsButton) {
                ttsButton.addEventListener('click', function () {
                    easycaptcha.tts(captcha);
                });
                ttsButton.style.display = null;
            }
        }

        captcha.classList.add(easycaptcha.classnames.initializedState);
    }

    function initAll() {
        var captchas = easycaptcha.getAll();
        for (var i = 0; i < captchas.length; i++) {
            easycaptcha.init(captchas[i]);
        }
    }

    var easycaptchaObject = {
        logging: true,
        classnames: {
            container: 'easycaptcha',
            initializedState: 'easycaptcha--initialized',
            image: 'easycaptcha__image',
            reloadButton: 'easycaptcha__action-reload',
            ttsButton: 'easycaptcha__action-tts',
        },

        get: get,
        getAll: getAll,

        init: init,
        initAll: initAll,

        reload: reload,
        checkTtsSupport: checkTtsSupport,
        tts: tts,
    };

    if (! window.easycaptcha) {
        window.easycaptcha = {};
    }

    for (var prop in easycaptchaObject) {
        if (window.easycaptcha[prop] === undefined) {
            window.easycaptcha[prop] = easycaptchaObject[prop];
        }
    }
})();
