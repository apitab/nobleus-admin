<?php

namespace backend\controllers;

use yii;
use backend\models\CustomerRequests;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\CustomerRequestSearchForm;
use backend\models\Vendors;
use backend\helpers\StatusCodes;
use backend\models\CustomerAddress;
use backend\models\Payments;
use backend\models\OutboundSms;
use backend\helpers\Helpers;
use backend\models\VendorNotifications;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
/**
 * CustomerRequestsController implements the CRUD actions for CustomerRequests model.
 */
class CustomerRequestsController extends CustomController
{
    public $layout = "dashboard/main";

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
     * Lists all CustomerRequests models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new CustomerRequestSearchForm();
        $query = CustomerRequests::find();
        $query->alias('cr');
        $query->innerJoin('customers c', 'c.id = cr.customer_id');
        $query->innerJoin('vendors v', 'v.id = cr.vendor_id');
        $search = false;
        $searchText = '';

        if ($this->request->get('CustomerRequestSearchForm')) {
            $search = true;
            $searchForm = $this->request->get('CustomerRequestSearchForm');

            $model->customer_phone_number = isset($searchForm['customer_phone_number']) ? $searchForm['customer_phone_number'] : '';
            $model->vendor_phone_number = isset($searchForm['vendor_phone_number']) ? $searchForm['vendor_phone_number'] : '';
            $model->date_from = isset($searchForm['date_from']) ? $searchForm['date_from'] : '';
            $model->date_to = isset($searchForm['date_to']) ? $searchForm['date_to'] : '';
            $model->status = isset($searchForm['status']) ? $searchForm['status'] : '';

            if ($model->customer_phone_number != "") {
                $model->customer_phone_number = preg_replace('/[^0-9]/', '', $model->customer_phone_number);
                $query->andWhere(['like', 'c.phone_number', "%" . $model->customer_phone_number . "%", false]);
                $searchText .= "/ Customer Phone:  {$model->customer_phone_number}";
            }

            if ($model->vendor_phone_number != "") {
                $model->vendor_phone_number = preg_replace('/[^0-9]/', '', $model->vendor_phone_number);
                $query->andWhere(['like', 'v.mobile_number', "%" . $model->vendor_phone_number . "%", false]);
                $searchText .= "/ Vendor Phone:  {$model->vendor_phone_number}";
            }

            if (!empty($model->date_from)) {
                $query->andWhere(['>=', 'cr.date_created', $model->date_from]);
                $searchText .= "/ Date From:  {$model->date_from}";
            }

            if (!empty($model->date_to)) {
                $query->andWhere(['<=', 'cr.date_created', $model->date_to]);
                $searchText .= "/ Date To:  {$model->date_to}";
            }

            if ($model->status != "") {
                $query->andWhere("cr.status = :status", [":status" => $model->status]);
            }
        }



        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'search' => $search,
            'searchText' => $searchText,
        ]);
    }

    /**
     * Displays a single CustomerRequests model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $payment = Payments::find()->where(['request_id' => $model->id])->one();

        return $this->render('view', [
            'model' => $model,
            'paymentDetails' => $payment ?? null
        ]);
    }

    /**
     * Updates an existing CustomerRequests model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $cid)
    {
        $model = $this->findModel($id);
        if ($model->customer_id != $cid) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }

        $addresses = CustomerAddress::findAll(['status' => StatusCodes::ACTIVE_STATUS, 'customer_id' => $cid]);

        if ($this->request->isPost) {
            $data = $this->request->post();
            $model->load($this->request->post());
            $model->customer_id = $cid;
            $oldVendorId = $model->vendor_id;
            $model->vendor_id = $data['vendorId'];

            //Get the coordinates and description from POST data
            // Get the coordinates and description from POST data
            $coordinates = Yii::$app->request->post('addressCoordinates');
            $description = Yii::$app->request->post('addressName');
            $addressId = Yii::$app->request->post('addressId');

            if ($addressId) {
                $model->customer_address = $addressId;
            } else {
                $address = new CustomerAddress();
                $address->customer_id = $cid;
                $address->location_id = 1;
                $address->address = $description;
                $address->location_coordinates = $coordinates;
                $address->date_created = $address->date_modified = Date('Y-m-d H:i:s');
                $address->status = StatusCodes::ACTIVE_STATUS;
                $address->save();
                $model->customer_address = $address->id;
            }

            if ($model->save()) {
                //If the vendorId is new, send notification to vendor
                if ($model->vendor_id != $oldVendorId) {
                    //Send notification to vendor
                    //Send SMS to the vendor
                    $template = Helpers::getSMSTemplate('VENDOR_ORDER_CREATED');
                    $template = str_replace("%name%", $model->vendor->other_names, $template);
                    $template = str_replace("%ref%", $model->id, $template);
                    $template = str_replace("%volume%", $model->volume_requested, $template);
                    $template = str_replace("%address%", $model->customerAddress->address, $template);
                    $template = str_replace("%date%", date('d/m/Y', strtotime($model->delivery_date)), $template);
                    $sms = new OutboundSms();
                    $sms->msisdn = $model->vendor->mobile_number;
                    $sms->message = $template;
                    $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
                    $sms->status = 'pending';
                    $sms->save();

                    //Add app notifications for vendor and user
                    $vendorNotification = new VendorNotifications();
                    $vendorNotification->vendor_id = $model->vendor_id;
                    $vendorNotification->title = "New Order";
                    $vendorNotification->message = $sms->message;
                    $vendorNotification->type = "order";
                    $vendorNotification->status = StatusCodes::CREATE_STATUS;
                    $vendorNotification->date_created = $sms->date_modified = Date("Y-m-d H:i:s");
                    $vendorNotification->save();

                }

                Yii::$app->session->setFlash('success', 'Customer request has been updated successfully');
                return $this->redirect(['customers/requests', 'id' => $model->customer_id, 'updated' => $model->id]);
            }

        }

        //Find the list of vendor locations
        $vendorLocations = Vendors::find()
            ->select('id, other_names, first_name, location_coordinates')
            ->where(['status' => StatusCodes::ACTIVE_STATUS])
            ->all();

        return $this->render('update', [
            'model' => $model,
            'vendorLocations' => $vendorLocations,
            'addresses' => $addresses
        ]);
    }

    /**
     * Deletes an existing CustomerRequests model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the CustomerRequests model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return CustomerRequests the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CustomerRequests::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function actionReport()
    {
        $model = new CustomerRequestSearchForm();
        $query = CustomerRequests::find();
        $query->alias('cr');
        $query->innerJoin('customers c', 'c.id = cr.customer_id');
        $query->innerJoin('vendors v', 'v.id = cr.vendor_id');

        // Apply the same filters as in actionIndex
        if ($this->request->get('CustomerRequestSearchForm')) {
            $searchForm = $this->request->get('CustomerRequestSearchForm');

            $model->customer_phone_number = isset($searchForm['customer_phone_number']) ? $searchForm['customer_phone_number'] : '';
            $model->vendor_phone_number = isset($searchForm['vendor_phone_number']) ? $searchForm['vendor_phone_number'] : '';
            $model->date_from = isset($searchForm['date_from']) ? $searchForm['date_from'] : '';
            $model->date_to = isset($searchForm['date_to']) ? $searchForm['date_to'] : '';
            $model->status = isset($searchForm['status']) ? $searchForm['status'] : '';

            if ($model->customer_phone_number != "") {
                $model->customer_phone_number = preg_replace('/[^0-9]/', '', $model->customer_phone_number);
                $query->andWhere(['like', 'c.phone_number', "%" . $model->customer_phone_number . "%", false]);
            }

            if ($model->vendor_phone_number != "") {
                $model->vendor_phone_number = preg_replace('/[^0-9]/', '', $model->vendor_phone_number);
                $query->andWhere(['like', 'v.mobile_number', "%" . $model->vendor_phone_number . "%", false]);
            }

            if (!empty($model->date_from)) {
                $query->andWhere(['>=', 'cr.date_created', $model->date_from]);
            }

            if (!empty($model->date_to)) {
                $query->andWhere(['<=', 'cr.date_created', $model->date_to]);
            }

            if ($model->status != "") {
                $query->andWhere("cr.status = :status", [":status" => $model->status]);
            }
        }

        // Get all requests based on the query
        $requests = $query->orderBy(['cr.id' => SORT_DESC])->all();

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator(Yii::$app->name)
            ->setLastModifiedBy(Yii::$app->name)
            ->setTitle('Customer Requests Report')
            ->setSubject('Customer Requests Report')
            ->setDescription('Customer requests report generated from ' . Yii::$app->name);

        // Add report title
        $sheet->setCellValue('A1', 'Customer Requests Report');
        $sheet->setCellValue('A2', 'Generated on: ' . date('Y-m-d H:i:s'));

        // Merge cells for title
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');

        // Style the header
        $sheet->getStyle('A1:H2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Add filters information if any
        $currentRow = 3;
        if ($model->hasFilters()) {
            $sheet->setCellValue('A' . $currentRow, 'Applied Filters:');
            $currentRow++;
            if ($model->customer_phone_number) {
                $sheet->setCellValue('A' . $currentRow, 'Customer Phone: ' . $model->customer_phone_number);
                $currentRow++;
            }
            if ($model->vendor_phone_number) {
                $sheet->setCellValue('A' . $currentRow, 'Vendor Phone: ' . $model->vendor_phone_number);
                $currentRow++;
            }
            if ($model->date_from) {
                $sheet->setCellValue('A' . $currentRow, 'Date From: ' . $model->date_from);
                $currentRow++;
            }
            if ($model->date_to) {
                $sheet->setCellValue('A' . $currentRow, 'Date To: ' . $model->date_to);
                $currentRow++;
            }
            if ($model->status !== '') {
                $sheet->setCellValue('A' . $currentRow, 'Status: ' . StatusCodes::getStatusText($model->status));
                $currentRow++;
            }
            $currentRow++; // Add extra space
        }

        // Add headers
        $headers = [
            'A' => '#',
            'B' => 'Customer Name',
            'C' => 'Customer Phone',
            'D' => 'Vendor Name',
            'E' => 'Volume (L)',
            'F' => 'Amount (KES)',
            'G' => 'Status',
            'H' => 'Date Created'
        ];

        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . $currentRow, $header);
        }

        // Style the headers
        $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'F4F4F4',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Add data
        $row = $currentRow + 1;
        foreach ($requests as $index => $request) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $request->customer->alias);
            $sheet->setCellValue('C' . $row, $request->customer->phone_number);
            $sheet->setCellValue('D' . $row, $request->vendor->other_names);
            $sheet->setCellValue('E' . $row, $request->volume_requested);
            $sheet->setCellValue('F' . $row, $request->total_amount);
            $sheet->setCellValue('G' . $row, StatusCodes::getStatusText($request->status));
            $sheet->setCellValue('H' . $row, Yii::$app->formatter->asDate($request->date_created));

            // Style the status cell based on the status
            $statusColor = '000000'; // Default black
            if ($request->status === StatusCodes::PAYMENT_SUCCESS) {
                $statusColor = '008000'; // Green
            } elseif ($request->status === StatusCodes::PAYMENT_FAILED) {
                $statusColor = 'FF0000'; // Red
            }
            $sheet->getStyle('G' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($statusColor));

            $row++;
        }

        // Add total row
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Requests:');
        $sheet->setCellValue('B' . $row, count($requests));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Customer_Requests_Report_' . date('Y-m-d_H-i-s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Save file to PHP output
        $writer->save('php://output');
        exit();
    }
}
