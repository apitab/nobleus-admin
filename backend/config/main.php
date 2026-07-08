<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'hwa-dhaamiye-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'billing' => [
            'class' => 'backend\modules\billing\Module',
        ],
    ],
    'language' => 'en',
    'components' => [
        'assetManager' => [
            'bundles' => [
                // jQuery is already bundled in AppAsset (lib/jquery); don't load it twice
                'yii\web\JqueryAsset' => [
                    'js' => [],
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
            ],
        ],
        'request' => [
            'csrfParam' => '_csrf-backend',
            'enableCsrfValidation' => false,
        ],
        'user' => [
            'identityClass' => 'backend\models\Users',
            'enableAutoLogin' => false,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'hwa-dhaamiye-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [],
        ]
    ],
    // Restrict the billing domain to the billing module only.
    // The full interface is available on the portal domain.
    'on beforeAction' => function ($event) {
        $host = Yii::$app->request->hostName;
        $billingHost = parse_url($_ENV['BILLING_PORTAL_URL'] ?? '', PHP_URL_HOST) ?: 'billing.hargeisawatertech.com';
        if ($host !== $billingHost) {
            return;
        }
        $route = $event->action->controller->route;
        $allowedRoutes = [
            'site/index', 'site/login', 'site/logout', 'site/error',
            'site/request-password-reset', 'site/reset-password',
            'dashboard/settings', 'dashboard/change-password',
        ];
        if (strpos($route, 'billing/') !== 0 && !in_array($route, $allowedRoutes, true)) {
            $event->isValid = false;
            Yii::$app->response->redirect(['/billing/dashboard/index'])->send();
        }
    },
    'params' => $params,
];
