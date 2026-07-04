<?php

namespace backend\modules\billing\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\modules\billing\models\ReadingSearch;
use common\models\billing\MeterReadingRaw;

/**
 * ReadingsController implements the CRUD actions for MeterReadingRaw model.
 */
class ReadingsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'post-to-billing' => ['POST'],
                ],
            ],
        ];
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
            $params['ReadingSearch']['supply_no'] = trim($zone . '-' . $area . '-' . $customer, '-');
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

        $billingUrl = Yii::$app->params['billingApiUrl']
            ?? 'https://core.hwa-smartmetering-app.com/bill/api/meter-reading';
        $billingToken = Yii::$app->params['billingApiToken'] ?? '';

        // Latest reading per supply_no on or before the 28th of the target month
        $rows = MeterReadingRaw::find()
            ->where(['<=', 'reading_time', $targetEnd])
            ->orderBy(['reading_time' => SORT_DESC])
            ->all();

        $posted = [];
        $skipped = [];
        $seen = [];

        foreach ($rows as $reading) {
            $supplyNo = $reading->supply_no;
            if ($supplyNo === null || $supplyNo === '' || isset($seen[$supplyNo])) {
                continue;
            }
            $seen[$supplyNo] = true;

            $m3 = (int) floor((float) $reading->reading_value / 1000);

            // Skip zero readings to avoid false data in the billing server
            if ($m3 === 0) {
                $skipped[] = ['supply_no' => $supplyNo, 'reason' => 'zero reading'];
                continue;
            }

            $readingDate = date('Y-m-d', strtotime($reading->reading_time));
            $description = null;
            if ($readingDate !== $targetDate) {
                $description = 'No reading on ' . $targetDate
                    . '; used last available reading from ' . $readingDate . '.';
            }

            $payload = [
                'meterNo' => $supplyNo,
                'reading' => $m3,
                'date' => $targetDate,
            ];
            if ($description !== null) {
                $payload['description'] = $description;
            }

            $ok = $this->sendToBilling($billingUrl, $billingToken, $payload);
            if ($ok) {
                $posted[] = $payload;
            } else {
                $skipped[] = ['supply_no' => $supplyNo, 'reason' => 'billing API error'];
            }
        }

        Yii::$app->session->setFlash('success',
            count($posted) . ' reading(s) posted for ' . $targetDate . ', ' . count($skipped) . ' skipped.');

        return $this->redirect(['index']);
    }

    /**
     * Send a single reading payload to the billing API.
     * @return bool true on HTTP 2xx
     */
    protected function sendToBilling($url, $token, array $payload)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_TIMEOUT => 15,
        ]);
        curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
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
