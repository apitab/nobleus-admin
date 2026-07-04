<?php

namespace backend\modules\billing\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use common\models\billing\BillingAuditLog;
use common\models\billing\BillingLedger;
use common\models\billing\MeterReadingRaw;
use backend\modules\billing\models\ReadingSearch;

class ReadingsController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new ReadingSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * Move a pending raw reading into the finalized billing ledger.
     */
    public function actionImport($id)
    {
        $model = $this->findModel($id);

        if ($model->status == MeterReadingRaw::STATUS_IMPORTED) {
            Yii::$app->session->setFlash('warning', Yii::t('app', 'This reading has already been posted.'));
            return $this->redirect(['index']);
        }
        if (empty($model->supply_no)) {
            Yii::$app->session->setFlash('danger', Yii::t('app', 'Cannot import: this meter has no supply number assigned.'));
            return $this->redirect(['index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $previous = BillingLedger::find()
                ->where(['supply_no' => $model->supply_no])
                ->andWhere(['<', 'reading_date', date('Y-m-d', strtotime($model->reading_time))])
                ->orderBy(['reading_date' => SORT_DESC, 'id' => SORT_DESC])
                ->one();

            $ledger = new BillingLedger();
            $ledger->raw_reading_id = $model->id;
            $ledger->meter_id = $model->meter_id;
            $ledger->supply_no = $model->supply_no;
            $ledger->previous_reading = $previous ? $previous->current_reading : null;
            $ledger->current_reading = $model->reading_value;
            $ledger->consumption = $previous !== null
                ? (float)$model->reading_value - (float)$previous->current_reading
                : null;
            $ledger->reading_date = date('Y-m-d', strtotime($model->reading_time));
            $ledger->imported_by = Yii::$app->user->id;
            $ledger->imported_at = date('Y-m-d H:i:s');

            if (!$ledger->save()) {
                throw new \RuntimeException('Ledger save failed: ' . json_encode($ledger->errors));
            }

            $model->status = MeterReadingRaw::STATUS_IMPORTED;
            $model->save(false);

            BillingAuditLog::record('import', [
                'supply_no' => $model->supply_no,
                'raw_reading_id' => $model->id,
                'ledger_id' => $ledger->id,
                'details' => 'Imported reading ' . $model->reading_value . ' for supply ' . $model->supply_no,
            ]);

            $transaction->commit();

            $pushMsg = $this->pushToCoreBilling($ledger);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Reading posted.') . ($pushMsg ? ' ' . $pushMsg : ''));
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('danger', Yii::t('app', 'Import failed. Please try again.'));
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    public function actionReject($id)
    {
        $model = $this->findModel($id);
        if ($model->status == MeterReadingRaw::STATUS_PENDING) {
            $model->status = MeterReadingRaw::STATUS_REJECTED;
            $model->save(false);
            BillingAuditLog::record('reject', [
                'supply_no' => $model->supply_no,
                'raw_reading_id' => $model->id,
            ]);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Reading rejected.'));
        }
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * Pushes a newly imported ledger entry to the core billing API
     * (reading.hargeisawatertech.com). Failures never block the import;
     * failed pushes can be retried with `php yii billing-push`.
     */
    protected function pushToCoreBilling(BillingLedger $ledger)
    {
        /** @var \common\components\CoreBillingClient $client */
        $client = Yii::$app->coreBilling;
        if (!$client->isConfigured()) {
            return null;
        }

        $result = $client->postMeterReading($ledger->supply_no, (float)$ledger->current_reading, $ledger->reading_date);

        $ledger->push_status = $result['reason'] ?: ($result['success'] ? 'pushed' : 'failed');
        $ledger->pushed_at = $result['success'] ? date('Y-m-d H:i:s') : null;
        $ledger->push_response = is_string($result['body']) ? $result['body'] : json_encode($result['body']);
        $ledger->save(false);

        BillingAuditLog::record($result['success'] ? 'push' : 'push_failed', [
            'supply_no' => $ledger->supply_no,
            'ledger_id' => $ledger->id,
            'details' => $result['message'],
        ]);

        if ($result['success']) {
            return Yii::t('app', 'Pushed to core billing.');
        }
        if ($result['reason'] === 'already_posted') {
            return Yii::t('app', 'Core billing: reading already posted for this supply.');
        }
        if ($result['reason'] === 'same_reading') {
            return Yii::t('app', 'Core billing: previous reading is the same as current.');
        }
        return Yii::t('app', 'Warning: push to core billing failed, will be retried.');
    }

    protected function findModel($id)
    {
        if (($model = MeterReadingRaw::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('app', 'The requested reading does not exist.'));
    }
}
