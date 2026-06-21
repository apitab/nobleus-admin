<?php

namespace backend\controllers;

use backend\helpers\Helpers;
use backend\helpers\StatusCodes;
use backend\models\Groups;
use backend\models\OutboundSms;
use yii;
use backend\models\Users;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use backend\models\OutboundEmails;
use backend\models\PasswordResetRequestForm;
use backend\models\UserSearchForm;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UsersController extends CustomController
{

    public $layout = 'dashboard/main';
    
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Users models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new UserSearchForm();
        $search = false;
        $query = Users::find();
        if($this->request->get('UserSearchForm')) {
            $search = true;
            //@todo validate this params
            $searchForm = $this->request->get('UserSearchForm');
            $model->phone_number = $searchForm['phone_number'];
            $model->email_address = $searchForm['email_address'];
            $model->group = $searchForm['group'];
            $model->status = $searchForm['status'];

            //Define the query parameters
            if($model->phone_number != "") {
                $model->phone_number = preg_replace('/[^0-9]/', '', $model->phone_number);
                $query->andWhere(['like','phone_number', "%".$model->phone_number."%", false]);
            }

            if($model->email_address != "") {
                $query->andWhere(['like','email_address', "%".$model->email_address."%", false]);
            }

            if($model->group != "") {
                $query->andWhere("group_id = :group_id",[":group_id" => $model->group]);
            }

            if($model->status != "") {
                $query->andWhere("status = :status",[":status" => $model->status]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'user_id' => SORT_DESC,
                ]
            ],
        ]);

        $groups = Groups::find()->all();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'groups' => $groups,
            'search' => $search
        ]);
    }

    /**
     * Displays a single Users model.
     * @param int $user_id User ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($user_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($user_id),
        ]);
    }

    /**
     * Creates a new Users model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Users();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->date_created = $model->date_modified = Date('Y-m-d H:i:s');
            $password = Yii::$app->security->generateRandomString(10);
            $model->password_hash = Yii::$app->security->generatePasswordHash($password);
            if ($model->save()) {
                //Send an email with the new password to the user
                $outbound_email = new OutboundEmails();
                $outbound_email->template = 'new_account';
                $outbound_email->payload = json_encode([
                    'to' => $model->email_address,
                    'password' => $password
                ]);
                $outbound_email->status = 1;
                $outbound_email->date_created = $outbound_email->date_modified = Date('Y-m-d H:i:s');
                $outbound_email->save();

                Yii::$app->session->setFlash('success','User has been created successfully and email sent to the user');
                return $this->redirect(['users/index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        //Load the list of groups
        $groups = Groups::find()->all();

        return $this->render('create', [
            'model' => $model,
            'groups' => $groups
        ]);
    }

    /**
     * Updates an existing Users model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $user_id User ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($user_id)
    {
        $model = $this->findModel($user_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success','User has been updated successfully');
            return $this->redirect(['users/index']);
        }

        //Load the list of groups
        $groups = Groups::find()->all();

        return $this->render('update', [
            'model' => $model,
            'groups' => $groups
        ]);
    }

    /**
     * Deletes an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $user_id User ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($user_id)
    {
        $user = $this->findModel($user_id);
        $user->status = StatusCodes::DELETE_STATUS;
        $user->save();

        Yii::$app->session->setFlash('success','User account has been de-activated succesfully');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $user_id User ID
     * @return Users the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($user_id)
    {
        if (($model = Users::findOne(['user_id' => $user_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Reset a users password
     */
    public function actionResetPassword($id) {
        $user = $this->findModel($id);

        $newPassword = Yii::$app->security->generateRandomString(9);
        $user->password_hash = Yii::$app->security->generatePasswordHash($newPassword);
        if($user->save()) {
            $sms = new OutboundSms();
            $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
            $sms->status  = 'pending';
            $sms->msisdn = Helpers::formatMsisdn($user->phone_number);
            $sms->message = Helpers::getSMSTemplate('USER_ACCOUNT_RESET');
            $sms->message = str_replace('%name%', $user->names, $sms->message);
            $sms->message = str_replace('%phone%', $user->phone_number, $sms->message);
            $sms->message = str_replace('%password%', $newPassword, $sms->message);
            $sms->save();

            Yii::$app->session->setFlash('success','User password has been reset successfully and sms sent to the user');
        } else {
            Yii::$app->session->setFlash('error','Error resetting users password. Ask user to reset their password from the forgot password link on the login page');
        }

        return $this->redirect(['users/index']);
        
    }
}
