<?php

namespace backend\modules\billing\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UnauthorizedHttpException;
use backend\models\Modules;
use backend\models\ModuleActions;
use backend\models\Permissions;

/**
 * Base controller for the billing module. Enforces the app's data-driven
 * RBAC: administrators (group_id = 1) get full access, other groups need a
 * permission row for module "billing" and action "<controller>/<action>".
 */
class BaseController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (isset(Yii::$app->user->identity->language)) {
            Yii::$app->language = Yii::$app->user->identity->language;
        }

        $identity = Yii::$app->user->identity;
        if ($identity && (int)$identity->group_id !== 1) {
            $module = Modules::findOne(['module_name' => 'billing']);
            $permName = $this->id . '/' . $action->id;
            $moduleAction = $module
                ? ModuleActions::findOne(['name' => $permName, 'module_id' => $module->id])
                : null;
            $permission = $moduleAction
                ? Permissions::findOne([
                    'group_id' => $identity->group_id,
                    'module_id' => $module->id,
                    'action_id' => $moduleAction->id,
                ])
                : null;
            if (!$permission) {
                throw new UnauthorizedHttpException(Yii::t('app', 'You do not have permissions to access this module'));
            }
        }

        return true;
    }

    public function isAdmin()
    {
        return !Yii::$app->user->isGuest && (int)Yii::$app->user->identity->group_id === 1;
    }
}
