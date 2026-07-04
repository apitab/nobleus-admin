<?php

namespace backend\modules\billing\controllers;

use backend\helpers\StatusCodes;
use backend\models\Customers;
use common\models\billing\BillingLedger;
use common\models\billing\Meter;
use common\models\billing\MeterReadingRaw;

class DashboardController extends BaseController
{
    public function actionIndex()
    {
        $today = date('Y-m-d');

        return $this->render('index', [
            'metersReadToday' => MeterReadingRaw::find()
                ->where(['>=', 'reading_time', $today . ' 00:00:00'])
                ->count('DISTINCT dev_eui'),
            'pendingImports' => MeterReadingRaw::find()
                ->where(['status' => MeterReadingRaw::STATUS_PENDING])
                ->count(),
            'importedToday' => BillingLedger::find()
                ->where(['reading_date' => $today])
                ->count(),
            'activeCustomers' => Customers::find()
                ->where(['status' => StatusCodes::ACTIVE_STATUS])
                ->count(),
            'totalMeters' => Meter::find()->count(),
            'recentReadings' => MeterReadingRaw::find()
                ->orderBy(['reading_time' => SORT_DESC])
                ->limit(8)
                ->all(),
        ]);
    }
}
