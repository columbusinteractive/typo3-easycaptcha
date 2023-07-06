<?php
return [
    'frontend' => [
        'columbusinteractive/middleware/easycaptcha' => [
            'target' => ColumbusInteractive\EasyCaptcha\Middleware\EasyCaptcha::class,
            'before' => [
                'typo3/cms-frontend/eid',
                'typo3/cms-frontend/tsfe',
            ],
        ],
    ],
];
