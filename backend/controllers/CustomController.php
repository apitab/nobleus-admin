<?php

namespace backend\controllers;

use yii\web\UnauthorizedHttpException;
use backend\models\ModuleActions;
use backend\models\Modules;
use backend\models\Permissions;
use Exception;
use yii;

class CustomController extends \yii\web\Controller
{
    public function beforeAction($event)
    {
        if(isset(Yii::$app->user->identity->language)) {
            Yii::$app->language = Yii::$app->user->identity->language;
        }
    
        return parent::beforeAction($event);
    }

    public function behaviors()
    {
        if (!Yii::$app->user->isGuest) {
            try {
                if (Yii::$app->user->identity->group_id != 1) {
                    $module = Modules::findOne(['module_name' => Yii::$app->controller->id]);
                    $action = ModuleActions::findOne(['name' => Yii::$app->controller->action->id, 'module_id' => $module->id]);
                    $group_id = Yii::$app->user->identity->group_id;

                    $permission = Permissions::findOne(['group_id' => $group_id, 'action_id' => $action->id, 'module_id' => $module->id]);
                    if (!$permission) {
                        throw new UnauthorizedHttpException(Yii::t('app','You do not have permissions to access this module'));
                    }
                }
            } catch (Exception $e) {
                throw new UnauthorizedHttpException(Yii::t('app','You do not have permissions to access this module'));
            }
        }


        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
}
