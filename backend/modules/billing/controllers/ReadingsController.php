<?php

namespace backend\modules\billing\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\modules\billing\models\ReadingSearch;
use common\models\billing\MeterReadingRaw;
use common\models\billing\BillingLedger;
use common\models\billing\BillingAuditLog;

/**
 * ReadingsController implements the CRUD actions for MeterReadingRaw model.
 */
class ReadingsController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'post-to-billing' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * Lists all MeterReadingRaw models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ReadingSearch();

        // Handle 3-part supply number form submission (zone - area - customer)
        $params = Yii::$app->request->queryParams;
        $search = $params['ReadingSearch'] ?? [];
        $zone = trim((string) ($search['supply_part1'] ?? ''));
        $area = trim((string) ($search['supply_part2'] ?? ''));
        $customer = trim((string) ($search['supply_part3'] ?? ''));

        // Combine the three parts into supply_no format
        if ($zone !== '' || $area !== '' || $customer !== '') {
            $params['ReadingSearch']['supply_no'] = trim($zone . ' - ' . $area . ' - ' . $customer, ' -');
        }

        // Default the date filter to today's date on first load; user-picked dates override this
        if (empty($params['ReadingSearch']['date_from']) && empty($params['ReadingSearch']['date_to'])) {
            $today = date('Y-m-d');
            $params['ReadingSearch']['date_from'] = $today;
            $params['ReadingSearch']['date_to'] = $today;
        }

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Post readings to the HWA billing server for a given billing month.
     *
     * Rules:
     *  - Target the 28th of the given month (defaults to current month).
     *  - Use the reading taken on the 28th; if none exists, fall back to the
     *    latest reading on/before the 28th and attach a description noting the
     *    actual reading date.
     *  - Convert raw liters to cubic metres and post floor(m3) as an integer.
     *  - Skip readings that floor to 0 m3 (never sent to billing).
     *
     * @param string|null $month Billing month as 'Y-m' (e.g. 2026-04); defaults to current month.
     * @return mixed
     */
    public function actionPostToBilling($month = null)
    {
        $month = $month ?: date('Y-m');
        $targetDate = $month . '-28';
        $targetEnd = $targetDate . ' 23:59:59';

        /** @var \common\components\CoreBillingClient $client */
        $client = Yii::$app->coreBilling;
        if (!$client->isConfigured()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Core billing API is not configured (CORE_READING_API_URL / CORE_API_TOKEN).'));
            return $this->redirect(['index']);
        }

        // Latest reading per meter on or before the 28th of the target month
        $rows = MeterReadingRaw::find()
            ->with('meter.assignment')
            ->where(['<=', 'reading_time', $targetEnd])
            ->orderBy(['reading_time' => SORT_DESC])
            ->all();

        $posted = 0;
        $failed = 0;
        $skipped = 0;
        $seen = [];
        $userId = Yii::$app->user->id;
        $now = date('Y-m-d H:i:s');

        foreach ($rows as $reading) {
            // Resolve supply_no from meter assignment (canonical source with correct spacing)
            $supplyNo = $reading->meter->assignment->supply_no ?? $reading->supply_no;
            if ($supplyNo === null || $supplyNo === '' || isset($seen[$supplyNo])) {
                continue;
            }
            $seen[$supplyNo] = true;

            $readingLiters = (float) $reading->reading_value;
            $m3 = (int) floor($readingLiters / 1000);

            // Skip zero readings to avoid false data in the billing server
            if ($m3 === 0) {
                $skipped++;
                continue;
            }

            // Avoid creating duplicate ledger entries if this reading was already imported
            if (BillingLedger::find()->where(['raw_reading_id' => $reading->id])->exists()) {
                $skipped++;
                continue;
            }

            $readingDate = date('Y-m-d', strtotime($reading->reading_time));

            // Previous reading for this supply (same logic as single import)
            $previous = BillingLedger::find()
                ->where(['supply_no' => $supplyNo])
                ->orderBy(['reading_date' => SORT_DESC, 'id' => SORT_DESC])
                ->one();

            $ledger = new BillingLedger();
            $ledger->raw_reading_id = $reading->id;
            $ledger->meter_id = $reading->meter_id;
            $ledger->supply_no = $supplyNo;
            $ledger->previous_reading = $previous ? $previous->current_reading : null;
            $ledger->current_reading = $readingLiters;
            $ledger->consumption = $previous ? max(0, $readingLiters - (float) $previous->current_reading) : null;
            $ledger->reading_date = $targetDate;
            $ledger->imported_by = $userId;
            $ledger->imported_at = $now;

            if (!$ledger->save()) {
                Yii::error('Bulk billing could not create ledger for reading ' . $reading->id . ': ' . json_encode($ledger->errors), __METHOD__);
                $failed++;
                continue;
            }

            $description = null;
            if ($readingDate !== $targetDate) {
                $description = 'No reading on ' . $targetDate
                    . '; used last available reading from ' . $readingDate . '.';
            }

            // Push to the core billing server
            $result = $client->postMeterReading($supplyNo, $readingLiters, $targetDate, $description);

            $ledger->push_status = $result['reason'] ?: ($result['success'] ? 'pushed' : 'failed');
            $ledger->pushed_at = $result['success'] ? date('Y-m-d H:i:s') : null;
            $ledger->push_response = is_string($result['body']) ? $result['body'] : json_encode($result['body']);
            $ledger->save(false);

            BillingAuditLog::record($result['success'] ? 'bulk_import' : 'bulk_import_push_failed', [
                'supply_no' => $supplyNo,
                'ledger_id' => $ledger->id,
                'details' => $result['message'],
            ]);

            if ($result['success']) {
                $posted++;
            } else {
                $failed++;
            }

            // Mark the raw reading as imported (approved); failed pushes are
            // retried automatically by the billing-push console command.
            $reading->status = MeterReadingRaw::STATUS_IMPORTED;
            $reading->save(false, ['status']);
        }

        if ($failed > 0) {
            Yii::$app->session->setFlash('warning',
                $posted . ' posted, ' . $failed . ' failed (queued for retry), ' . $skipped . ' skipped for ' . $targetDate . '.');
        } else {
            Yii::$app->session->setFlash('success',
                $posted . ' reading(s) posted for ' . $targetDate . ', ' . $skipped . ' skipped.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Posts a single pending reading to the billing API and marks it as imported.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionImport($id)
    {
        $model = $this->findModel($id);

        // GET requests (e.g. from browser history) just show the reading page
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ((int) $model->status !== MeterReadingRaw::STATUS_PENDING) {
            Yii::$app->session->setFlash('warning', Yii::t('app', 'Only pending readings can be posted.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $supplyNo = $model->meter->assignment->supply_no ?? $model->supply_no;
        if ($supplyNo === null || $supplyNo === '') {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Reading has no supply number; cannot post to billing.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $readingLiters = (float) $model->reading_value;
        if ((int) floor($readingLiters / 1000) === 0) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Zero reading; not posted to billing.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        /** @var \common\components\CoreBillingClient $client */
        $client = Yii::$app->coreBilling;
        if (!$client->isConfigured()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Core billing API is not configured (CORE_READING_API_URL / CORE_API_TOKEN).'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $readingDate = date('Y-m-d', strtotime($model->reading_time));

        // Company policy: always bill on the 28th of the reading's month.
        // If the reading was taken on a different day, attach a description
        // noting the actual reading date (same logic as the bulk action).
        $billingDate = date('Y-m', strtotime($model->reading_time)) . '-28';
        $description = null;
        if ($readingDate !== $billingDate) {
            $description = 'No reading on ' . $billingDate
                . '; used last available reading from ' . $readingDate . '.';
        }

        // Create the ledger entry (clerk approval record)
        $previous = BillingLedger::find()
            ->where(['supply_no' => $supplyNo])
            ->orderBy(['reading_date' => SORT_DESC, 'id' => SORT_DESC])
            ->one();

        $ledger = new BillingLedger();
        $ledger->raw_reading_id = $model->id;
        $ledger->meter_id = $model->meter_id;
        $ledger->supply_no = $supplyNo;
        $ledger->previous_reading = $previous ? $previous->current_reading : null;
        $ledger->current_reading = $readingLiters;
        $ledger->consumption = $previous ? max(0, $readingLiters - (float) $previous->current_reading) : null;
        $ledger->reading_date = $billingDate;
        $ledger->imported_by = Yii::$app->user->id;
        $ledger->imported_at = date('Y-m-d H:i:s');

        if (!$ledger->save()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Could not create ledger entry: ') . json_encode($ledger->errors));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Push to the core billing server using the 28th as the billing date
        $result = $client->postMeterReading($supplyNo, $readingLiters, $billingDate, $description);

        $ledger->push_status = $result['reason'] ?: ($result['success'] ? 'pushed' : 'failed');
        $ledger->pushed_at = $result['success'] ? date('Y-m-d H:i:s') : null;
        $ledger->push_response = is_string($result['body']) ? $result['body'] : json_encode($result['body']);
        $ledger->save(false);

        // Mark the raw reading as imported (approved); failed pushes are
        // retried automatically by the billing-push console command.
        $model->status = MeterReadingRaw::STATUS_IMPORTED;
        $model->save(false, ['status']);

        BillingAuditLog::record($result['success'] ? 'import' : 'import_push_failed', [
            'supply_no' => $supplyNo,
            'ledger_id' => $ledger->id,
            'details' => $result['message'],
        ]);

        if ($result['success']) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Reading posted to billing. ') . $result['message']);
        } else {
            Yii::$app->session->setFlash('warning', Yii::t('app', 'Reading approved, but the billing push failed and will be retried: ') . $result['message']);
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Rejects a single pending reading.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionReject($id)
    {
        $model = $this->findModel($id);

        // GET requests (e.g. from browser history) just show the reading page
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ((int) $model->status !== MeterReadingRaw::STATUS_PENDING) {
            Yii::$app->session->setFlash('warning', Yii::t('app', 'Only pending readings can be rejected.'));
        } else {
            $model->status = MeterReadingRaw::STATUS_REJECTED;
            $model->save(false, ['status']);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Reading rejected.'));
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Displays a single MeterReadingRaw model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new MeterReadingRaw model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MeterReadingRaw();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing MeterReadingRaw model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing MeterReadingRaw model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the MeterReadingRaw model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MeterReadingRaw the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MeterReadingRaw::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
