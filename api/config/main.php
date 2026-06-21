<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
);

return [
    'id' => 'hwa-app-api',
    'basePath' => dirname(__DIR__),    
    'bootstrap' => ['log'],
    'language' => 'en-US',
    'sourceLanguage' => 'en-US',
    'timeZone' => 'Africa/Mogadishu',
    'params' => [
        'languages' => [
            'en' => 'English',
            'so' => 'Somali',
        ],
    ],
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
            'class' => 'api\modules\v1\Module'
        ],
        'vendor' => [
            'basePath' => '@app/modules/vendor',
            'class' => 'api\modules\vendor\Module'
        ]
    ],
    'components' => [      
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@api/messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
            ],
        ],  
        'user' => [
            'identityClass' => 'backend\models\User',
            'enableAutoLogin' => false,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                
            ],        
        ],
        'request' => [
            // Disable CSRF for the entire application
            'enableCsrfValidation' => false,
        ]
    ],
    'params' => $params,
];



