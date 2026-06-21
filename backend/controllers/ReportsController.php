<?php 

namespace backend\controllers;

use Yii;
use backend\models\CustomerRequests;
use backend\models\Payments;
use backend\helpers\StatusCodes;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportsController extends CustomController
{
    public $layout = "dashboard/main";

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionRevenueSummary()
    {
        // Get parameters from request
        $params = Yii::$app->request->get();
        
        // Query customer requests with status 115 (PAYMENT_FAILED) and 116 (PAYMENT_RETRY)
        $query = CustomerRequests::find()
            ->alias('cr')
            ->innerJoin('customers c', 'c.id = cr.customer_id')
            ->innerJoin('vendors v', 'v.id = cr.vendor_id')
            ->leftJoin('payments p', 'p.request_id = cr.id')
            ->where(['cr.status' => [StatusCodes::PAYMENT_FAILED, StatusCodes::PAYMENT_RETRY]])
            ->select([
                'cr.*',
                'c.alias as customer_name',
                'c.phone_number as customer_phone',
                'v.other_names as vendor_name',
                'v.mobile_number as vendor_phone',
                'p.amount as payment_amount',
                'p.description as payment_description',
                'p.status as payment_status',
                'p.date_created as payment_date'
            ]);

        // Apply date filters if provided
        if (isset($params['date_from']) && !empty($params['date_from'])) {
            $query->andWhere(['>=', 'cr.date_created', $params['date_from']]);
        }
        
        if (isset($params['date_to']) && !empty($params['date_to'])) {
            $query->andWhere(['<=', 'cr.date_created', $params['date_to']]);
        }

        // Apply customer phone filter if provided
        if (isset($params['customer_phone']) && !empty($params['customer_phone'])) {
            $customerPhone = preg_replace('/[^0-9]/', '', $params['customer_phone']);
            $query->andWhere(['like', 'c.phone_number', "%{$customerPhone}%", false]);
        }

        // Apply vendor phone filter if provided
        if (isset($params['vendor_phone']) && !empty($params['vendor_phone'])) {
            $vendorPhone = preg_replace('/[^0-9]/', '', $params['vendor_phone']);
            $query->andWhere(['like', 'v.mobile_number', "%{$vendorPhone}%", false]);
        }

        $requests = $query->orderBy(['cr.id' => SORT_DESC])->all();

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator(Yii::$app->name)
            ->setLastModifiedBy(Yii::$app->name)
            ->setTitle('Revenue Summary Report')
            ->setSubject('Revenue Summary Report')
            ->setDescription('Revenue summary report for failed and retry payments generated from ' . Yii::$app->name);

        // Add report title
        $sheet->setCellValue('A1', 'Revenue Summary Report');
        $sheet->setCellValue('A2', 'Generated on: ' . date('Y-m-d H:i:s'));
        $sheet->setCellValue('A3', 'Status: Failed (115) and Retry (116) Payments');

        // Merge cells for title
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
        $sheet->mergeCells('A3:K3');

        // Style the header
        $sheet->getStyle('A1:K3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Add filters information if any
        $currentRow = 4;
        $hasFilters = false;
        if (isset($params['date_from']) || isset($params['date_to']) || isset($params['customer_phone']) || isset($params['vendor_phone'])) {
            $sheet->setCellValue('A' . $currentRow, 'Applied Filters:');
            $currentRow++;
            $hasFilters = true;
            
            if (isset($params['date_from']) && !empty($params['date_from'])) {
                $sheet->setCellValue('A' . $currentRow, 'Date From: ' . $params['date_from']);
                $currentRow++;
            }
            if (isset($params['date_to']) && !empty($params['date_to'])) {
                $sheet->setCellValue('A' . $currentRow, 'Date To: ' . $params['date_to']);
                $currentRow++;
            }
            if (isset($params['customer_phone']) && !empty($params['customer_phone'])) {
                $sheet->setCellValue('A' . $currentRow, 'Customer Phone: ' . $params['customer_phone']);
                $currentRow++;
            }
            if (isset($params['vendor_phone']) && !empty($params['vendor_phone'])) {
                $sheet->setCellValue('A' . $currentRow, 'Vendor Phone: ' . $params['vendor_phone']);
                $currentRow++;
            }
            $currentRow++; // Add extra space
        }

        // Add headers
        $headers = [
            'A' => '#',
            'B' => 'Request ID',
            'C' => 'Customer Name',
            'D' => 'Customer Phone',
            'E' => 'Vendor Name',
            'F' => 'Vendor Phone',
            'G' => 'Volume (L)',
            'H' => 'Request Amount (SOS)',
            'I' => 'Payment Amount (SOS)',
            'J' => 'Request Status',
            'K' => 'Payment Status',
            'L' => 'Request Date',
            'M' => 'Payment Date'
        ];

        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . $currentRow, $header);
        }

        // Style the headers
        $sheet->getStyle('A' . $currentRow . ':M' . $currentRow)->applyFromArray([
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
        $totalRequestAmount = 0;
        $totalPaymentAmount = 0;
        
        foreach ($requests as $index => $request) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $request->id);
            $sheet->setCellValue('C' . $row, $request->customer_name);
            $sheet->setCellValue('D' . $row, $request->customer_phone);
            $sheet->setCellValue('E' . $row, $request->vendor_name);
            $sheet->setCellValue('F' . $row, $request->vendor_phone);
            $sheet->setCellValue('G' . $row, $request->volume_requested);
            $sheet->setCellValue('H' . $row, $request->total_amount);
            $sheet->setCellValue('I' . $row, $request->payment_amount ?? 'N/A');
            $sheet->setCellValue('J' . $row, StatusCodes::getRequestStatusText($request->status));
            $sheet->setCellValue('K' . $row, $request->payment_status ? StatusCodes::getPaymentStatusText($request->payment_status) : 'N/A');
            $sheet->setCellValue('L' . $row, Yii::$app->formatter->asDate($request->date_created));
            $sheet->setCellValue('M' . $row, $request->payment_date ? Yii::$app->formatter->asDate($request->payment_date) : 'N/A');

            // Style the status cells based on the status
            $requestStatusColor = '000000'; // Default black
            $paymentStatusColor = '000000'; // Default black
            
            if ($request->status === StatusCodes::PAYMENT_FAILED) {
                $requestStatusColor = 'FF0000'; // Red
            } elseif ($request->status === StatusCodes::PAYMENT_RETRY) {
                $requestStatusColor = 'FFA500'; // Orange
            }
            
            if ($request->payment_status === StatusCodes::PAYMENT_FAILED) {
                $paymentStatusColor = 'FF0000'; // Red
            } elseif ($request->payment_status === StatusCodes::PAYMENT_RETRY) {
                $paymentStatusColor = 'FFA500'; // Orange
            }
            
            $sheet->getStyle('J' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($requestStatusColor));
            $sheet->getStyle('K' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($paymentStatusColor));

            $totalRequestAmount += $request->total_amount;
            if ($request->payment_amount) {
                $totalPaymentAmount += $request->payment_amount;
            }

            $row++;
        }

        // Add summary rows
        $row++;
        $sheet->setCellValue('A' . $row, 'Summary:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Total Requests:');
        $sheet->setCellValue('B' . $row, count($requests));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Total Request Amount:');
        $sheet->setCellValue('B' . $row, number_format($totalRequestAmount, 2) . ' SOS');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Total Payment Amount:');
        $sheet->setCellValue('B' . $row, number_format($totalPaymentAmount, 2) . ' SOS');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Revenue_Summary_Report_' . date('Y-m-d_H-i-s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Save file to PHP output
        $writer->save('php://output');
        exit();
    }

    public function actionMonthlyRevenue()
    {
        // Get parameters from request
        $params = Yii::$app->request->get();
        
        // Get current month's start and end dates
        $currentMonth = date('Y-m');
        $monthStart = $currentMonth . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));
        
        // Query customer requests with status 115 (PAYMENT_FAILED) and 116 (PAYMENT_RETRY) for current month
        $query = CustomerRequests::find()
            ->alias('cr')
            ->innerJoin('customers c', 'c.id = cr.customer_id')
            ->innerJoin('vendors v', 'v.id = cr.vendor_id')
            ->leftJoin('payments p', 'p.request_id = cr.id')
            ->where(['cr.status' => [StatusCodes::PAYMENT_FAILED, StatusCodes::PAYMENT_RETRY]])
            ->andWhere(['>=', 'cr.date_created', $monthStart])
            ->andWhere(['<=', 'cr.date_created', $monthEnd . ' 23:59:59'])
            ->select([
                'cr.*',
                'c.alias as customer_name',
                'c.phone_number as customer_phone',
                'v.other_names as vendor_name',
                'v.mobile_number as vendor_phone',
                'p.amount as payment_amount',
                'p.description as payment_description',
                'p.status as payment_status',
                'p.date_created as payment_date'
            ]);

        // Apply additional filters if provided
        if (isset($params['customer_phone']) && !empty($params['customer_phone'])) {
            $customerPhone = preg_replace('/[^0-9]/', '', $params['customer_phone']);
            $query->andWhere(['like', 'c.phone_number', "%{$customerPhone}%", false]);
        }

        if (isset($params['vendor_phone']) && !empty($params['vendor_phone'])) {
            $vendorPhone = preg_replace('/[^0-9]/', '', $params['vendor_phone']);
            $query->andWhere(['like', 'v.mobile_number', "%{$vendorPhone}%", false]);
        }

        $requests = $query->orderBy(['cr.id' => SORT_DESC])->all();

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator(Yii::$app->name)
            ->setLastModifiedBy(Yii::$app->name)
            ->setTitle('Monthly Revenue Report')
            ->setSubject('Monthly Revenue Report')
            ->setDescription('Monthly revenue report for failed and retry payments generated from ' . Yii::$app->name);

        // Add report title
        $sheet->setCellValue('A1', 'Monthly Revenue Report');
        $sheet->setCellValue('A2', 'Generated on: ' . date('Y-m-d H:i:s'));
        $sheet->setCellValue('A3', 'Period: ' . date('F Y', strtotime($monthStart)));
        $sheet->setCellValue('A4', 'Status: Failed (115) and Retry (116) Payments');

        // Merge cells for title
        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        $sheet->mergeCells('A3:M3');
        $sheet->mergeCells('A4:M4');

        // Style the header
        $sheet->getStyle('A1:M4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Add filters information if any
        $currentRow = 5;
        $hasFilters = false;
        if (isset($params['customer_phone']) || isset($params['vendor_phone'])) {
            $sheet->setCellValue('A' . $currentRow, 'Applied Filters:');
            $currentRow++;
            $hasFilters = true;
            
            if (isset($params['customer_phone']) && !empty($params['customer_phone'])) {
                $sheet->setCellValue('A' . $currentRow, 'Customer Phone: ' . $params['customer_phone']);
                $currentRow++;
            }
            if (isset($params['vendor_phone']) && !empty($params['vendor_phone'])) {
                $sheet->setCellValue('A' . $currentRow, 'Vendor Phone: ' . $params['vendor_phone']);
                $currentRow++;
            }
            $currentRow++; // Add extra space
        }

        // Add headers
        $headers = [
            'A' => '#',
            'B' => 'Request ID',
            'C' => 'Customer Name',
            'D' => 'Customer Phone',
            'E' => 'Vendor Name',
            'F' => 'Vendor Phone',
            'G' => 'Volume (L)',
            'H' => 'Request Amount (SOS)',
            'I' => 'Payment Amount (SOS)',
            'J' => 'Request Status',
            'K' => 'Payment Status',
            'L' => 'Request Date',
            'M' => 'Payment Date'
        ];

        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . $currentRow, $header);
        }

        // Style the headers
        $sheet->getStyle('A' . $currentRow . ':M' . $currentRow)->applyFromArray([
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
        $totalRequestAmount = 0;
        $totalPaymentAmount = 0;
        $failedCount = 0;
        $retryCount = 0;
        
        foreach ($requests as $index => $request) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $request->id);
            $sheet->setCellValue('C' . $row, $request->customer_name);
            $sheet->setCellValue('D' . $row, $request->customer_phone);
            $sheet->setCellValue('E' . $row, $request->vendor_name);
            $sheet->setCellValue('F' . $row, $request->vendor_phone);
            $sheet->setCellValue('G' . $row, $request->volume_requested);
            $sheet->setCellValue('H' . $row, $request->total_amount);
            $sheet->setCellValue('I' . $row, $request->payment_amount ?? 'N/A');
            $sheet->setCellValue('J' . $row, StatusCodes::getRequestStatusText($request->status));
            $sheet->setCellValue('K' . $row, $request->payment_status ? StatusCodes::getPaymentStatusText($request->payment_status) : 'N/A');
            $sheet->setCellValue('L' . $row, Yii::$app->formatter->asDate($request->date_created));
            $sheet->setCellValue('M' . $row, $request->payment_date ? Yii::$app->formatter->asDate($request->payment_date) : 'N/A');

            // Style the status cells based on the status
            $requestStatusColor = '000000'; // Default black
            $paymentStatusColor = '000000'; // Default black
            
            if ($request->status === StatusCodes::PAYMENT_FAILED) {
                $requestStatusColor = 'FF0000'; // Red
                $failedCount++;
            } elseif ($request->status === StatusCodes::PAYMENT_RETRY) {
                $requestStatusColor = 'FFA500'; // Orange
                $retryCount++;
            }
            
            if ($request->payment_status === StatusCodes::PAYMENT_FAILED) {
                $paymentStatusColor = 'FF0000'; // Red
            } elseif ($request->payment_status === StatusCodes::PAYMENT_RETRY) {
                $paymentStatusColor = 'FFA500'; // Orange
            }
            
            $sheet->getStyle('J' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($requestStatusColor));
            $sheet->getStyle('K' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($paymentStatusColor));

            $totalRequestAmount += $request->total_amount;
            if ($request->payment_amount) {
                $totalPaymentAmount += $request->payment_amount;
            }

            $row++;
        }

        // Add summary rows
        $row++;
        $sheet->setCellValue('A' . $row, 'Monthly Summary:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Total Requests:');
        $sheet->setCellValue('B' . $row, count($requests));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Failed Requests:');
        $sheet->setCellValue('B' . $row, $failedCount);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Retry Requests:');
        $sheet->setCellValue('B' . $row, $retryCount);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Total Request Amount:');
        $sheet->setCellValue('B' . $row, number_format($totalRequestAmount, 2) . ' SOS');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Total Payment Amount:');
        $sheet->setCellValue('B' . $row, number_format($totalPaymentAmount, 2) . ' SOS');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Monthly_Revenue_Report_' . date('Y-m') . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Save file to PHP output
        $writer->save('php://output');
        exit();
    }
}


