<?php

namespace backend\controllers;

use backend\helpers\StatusCodes;
use backend\models\CustomerAddress;
use backend\models\CustomerRequests;
use yii;
use backend\models\Customers;
use backend\models\CustomerSearchForm;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\Vendors;
use backend\models\Complaints;
use backend\models\Notifications;
use backend\models\OutboundSms;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use backend\models\AppSettings;
/**
 * CustomersController implements the CRUD actions for Customers model.
 */
class CustomersController extends CustomController
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
     * Lists all Customers models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new CustomerSearchForm();
        $query = Customers::find();

        $search = false;
        if ($this->request->get('CustomerSearchForm')) {
            $search = true;
            //@todo validate this params
            $searchForm = $this->request->get('CustomerSearchForm');
            $model->phone_number = $searchForm['phone_number'];
            $model->status = $searchForm['status'];

            //Define the query parameters
            if ($model->phone_number != "") {
                $model->phone_number = preg_replace('/[^0-9]/', '', $model->phone_number);
                $query->andWhere(['like', 'phone_number', "%" . $model->phone_number . "%", false]);
            }

            if ($model->status != "") {
                $query->andWhere("status = :status", [":status" => $model->status]);
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
            'model' => $model
        ]);
    }

    /**
     * Displays a single Customers model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id, $tab = null)
    {
        $model = $this->findModel($id);
        $recent_requests = CustomerRequests::find()
            ->where(['customer_id' => $id])
            ->with(['vendor', 'payment', 'payment.paymentMethod'])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        $complaints = [];
        if ($tab == 'complaints') {
            $complaints = Complaints::find()
                ->where(['customer_id' => $id])
                ->orderBy(['id' => SORT_DESC])
                ->all();
        }

        return $this->render('view', [
            'model' => $model,
            'recent_requests' => $recent_requests,
            'complaints' => $complaints,
        ]);
    }

    /**
     * Send a custom message to a single customer (notification + SMS)
     * @param int $id Customer ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionSendMessage($id)
    {
        $customer = $this->findModel($id);

        if (!$this->request->isPost) {
            return $this->redirect(['view', 'id' => $id]);
        }

        $title = trim($this->request->post('title', Yii::t('app', 'Message from Demo')));
        $message = trim($this->request->post('message', ''));

        if ($message === '') {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Message cannot be empty.'));
            return $this->redirect(['view', 'id' => $id]);
        }

        $sentNotification = false;

        // Create in-app notification if device token exists
        if (!empty($customer->device_token)) {
            $notification = new Notifications();
            $notification->device_id = $customer->device_token;
            $notification->data_values = json_encode([
                'customer_id' => $customer->id,
            ]);
            $notification->key_type = Notifications::KEY_TYPE_CUSTOMER;
            $notification->key_id = $customer->id;
            $notification->title = $title;
            $notification->message = $message;
            $notification->type = 'message';
            $notification->not_type = Notifications::NOT_TYPE_ALERT;
            $notification->status = StatusCodes::CREATE_STATUS;
            $notification->is_read = 0;
            $notification->date_created = $notification->date_modified = Date('Y-m-d H:i:s');

            if ($notification->save()) {
                $sentNotification = true;
            } else {
                Yii::error('Failed to save notification for customer ' . $customer->id . ': ' . json_encode($notification->errors));
            }
        }

        // Create SMS if phone number exists
        if (!empty($customer->phone_number)) {
            $sms = new OutboundSms();
            $sms->msisdn = $customer->phone_number;
            $sms->message = $title . ': ' . $message;
            $sms->date_created = $sms->date_modified = Date('Y-m-d H:i:s');
            $sms->status = 'pending';

            if (!$sms->save()) {
                Yii::error('Failed to save SMS for customer ' . $customer->id . ': ' . json_encode($sms->errors));
            } else {
                $sentNotification = true;
            }
        }

        if ($sentNotification) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Message queued successfully for delivery.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Unable to send message. Customer has no valid contact details.'));
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Creates a new Customers model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Customers();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->date_created = $model->date_modified = Date('Y-m-d H:i:s');
            if ($model->validate() && $model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Customer has been created successfully'));
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Customers model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->validate() && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Customer has been updated successfully'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $customerAddresses = CustomerAddress::findAll(['customer_id' => $id]);
        return $this->render('update', [
            'model' => $model,
            'customerAddresses' => $customerAddresses
        ]);
    }

    /**
     * Deletes an existing Customers model.
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
     * Finds the Customers model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Customers the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Customers::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * Manage Customer requests
     */
    public function actionRequests($id, $updated = null)
    {
        $query = CustomerRequests::find()->where(['customer_id' => $id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ]
        ]);

        return $this->render('requests', [
            'model' => $this->findModel($id),
            'dataProvider' => $dataProvider,
            'updated' => $updated
        ]);
    }

    public function actionPayments($id)
    {
        return $this->render('payments', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionAddresses($id, $tab = null)
    {
        $customerAddresses = CustomerAddress::findAll(['customer_id' => $id]);

        $recent_requests = CustomerRequests::find()
            ->where(['customer_id' => $id])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        $complaints = [];
        if ($tab == 'complaints') {
            $complaints = Complaints::find()
                ->where(['customer_id' => $id])
                ->orderBy(['id' => SORT_DESC])
                ->all();
        }

        return $this->render('addresses', [
            'model' => $this->findModel($id),
            'customerAddresses' => $customerAddresses,
            'recent_requests' => $recent_requests,
            'complaints' => $complaints
        ]);
    }

    //Requests Management
    public function actionCreateRequest($cid)
    {
        $model = $this->findModel($cid);
        $requestModel = new CustomerRequests();

        //Fetch the list of addresses for this customer
        $addresses = CustomerAddress::findAll(['status' => StatusCodes::ACTIVE_STATUS, 'customer_id' => $cid]);

        if ($this->request->isPost) {
            $data = $this->request->post();
            $requestModel->load($data);
            $addressId = $data['addressId'] == '' ? null : $data['addressId'];
            $addressCoordinates = $data['addressCoordinates'];

            if (is_null($addressId)) {
                $address = new CustomerAddress();
                $address->customer_id = $cid;
                $address->location_id = 1;
                $address->address = $requestModel->customer_address;
                $address->location_coordinates = $addressCoordinates;
                $address->date_created = $address->date_modified = Date('Y-m-d H:i:s');
                $address->status = StatusCodes::ACTIVE_STATUS;
                $address->save();   
                $requestModel->customer_address = $address->id;
            } else {
                $requestModel->customer_address = $addressId;
            }

            
            $requestModel->vendor_id = $data['vendorId'];
            $requestModel->customer_id = $cid;
            $requestModel->status = StatusCodes::NEW_CUSTOMER_REQUEST;
            $requestModel->date_created = Date('Y-m-d H:i:s');

            //find price per barrel from AppSettings
            $pricePerBarrel = AppSettings::findOne(['id' => 1])->price_per_barrel;
            $requestModel->total_amount = $requestModel->volume_requested * ($requestModel->vendor->price_of_water != null ? $requestModel->vendor->price_of_water : $pricePerBarrel); 

            if ($requestModel->save()) {
                //@todo Vendor Notification
                $requestModel->sendVendorNotifications($requestModel->vendor, $requestModel);
                $requestModel->sendCustomerNotifications($model, $requestModel);
                Yii::$app->session->setFlash('success', Yii::t('app', 'Customer request has been created successfully'));
                return $this->redirect(['customers/view', 'id' => $cid]);
            }
        }

        

        //Find the list of vendor locations
        $vendorLocations = Vendors::find()
            ->select('id, other_names, first_name, location_coordinates')
            ->where(['status' => StatusCodes::ACTIVE_STATUS])
            ->all();

        return $this->render('create-request', [
            'model' => $model,
            'addresses' => $addresses,
            'requestModel' => $requestModel,
            'vendorLocations' => $vendorLocations
        ]);
    }

    public function actionReport()
    {
        $model = new CustomerSearchForm();
        $query = Customers::find();

        // Apply the same filters as in actionIndex
        if ($this->request->get('CustomerSearchForm')) {
            $searchForm = $this->request->get('CustomerSearchForm');
            $model->phone_number = $searchForm['phone_number'];
            $model->status = $searchForm['status'];

            if ($model->phone_number != "") {
                $model->phone_number = preg_replace('/[^0-9]/', '', $model->phone_number);
                $query->andWhere(['like', 'phone_number', "%" . $model->phone_number . "%", false]);
            }

            if ($model->status != "") {
                $query->andWhere("status = :status", [":status" => $model->status]);
            }
        }

        // Get all customers based on the query
        $customers = $query->orderBy(['id' => SORT_DESC])->all();

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator(Yii::$app->name)
            ->setLastModifiedBy(Yii::$app->name)
            ->setTitle('Customers Report')
            ->setSubject('Customers Report')
            ->setDescription('Customers report generated from ' . Yii::$app->name);

        // Add report title
        $sheet->setCellValue('A1', 'Customers Report');
        $sheet->setCellValue('A2', 'Generated on: ' . date('Y-m-d H:i:s'));

        // Merge cells for title
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');

        // Style the header
        $sheet->getStyle('A1:F2')->applyFromArray([
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
            if ($model->phone_number) {
                $sheet->setCellValue('A' . $currentRow, 'Phone Number: ' . $model->phone_number);
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
            'C' => 'Phone Number',
            'D' => 'Status',
            'E' => 'Joined Date',
            'F' => 'Total Orders'
        ];

        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . $currentRow, $header);
        }

        // Style the headers
        $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray([
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
        foreach ($customers as $index => $customer) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $customer->alias);
            $sheet->setCellValue('C' . $row, $customer->phone_number);
            $sheet->setCellValue('D' . $row, StatusCodes::getStatusText($customer->status));
            $sheet->setCellValue('E' . $row, Yii::$app->formatter->asDate($customer->date_created));
            $sheet->setCellValue('F' . $row, $customer->getOrdersCount());

            // Style the status cell
            $sheet->getStyle('D' . $row)->getFont()->setColor(
                new \PhpOffice\PhpSpreadsheet\Style\Color($customer->status ? '008000' : 'FF0000')
            );

            $row++;
        }

        // Add total row
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Customers:');
        $sheet->setCellValue('B' . $row, count($customers));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Customers_Report_' . date('Y-m-d_H-i-s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Save file to PHP output
        $writer->save('php://output');
        exit();
    }
}
