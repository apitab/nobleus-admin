<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\ViewHelper;
$this->title = 'Reports';
$this->params['breadcrumbs'][] = $this->title;

// Get the active tab from URL or default to 'revenue'
$activeTab = Yii::$app->request->get('tab', 'revenue');
?>
<div class="content pd-t-20">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <?= $this->render('//layouts/dashboard/_breadcrumbs', [
            'title' => Yii::t('app', 'Reports'),
            'links' => [
                ['title' => Yii::t('app', 'Reports'), 'active' => true]
            ]
        ]) ?>
        <?= ViewHelper::displayFlash(); ?>

        <div class="row">
            <div class="col-sm-12 col-md-12">
                <div class="reports-index">
                    <div class="card">
                        <div class="card-body">
                            <!-- Pills navigation -->
                            <ul class="nav nav-pills mb-4" id="reportTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= $activeTab === 'revenue' ? 'active' : '' ?>"
                                        id="revenue-tab" data-bs-toggle="pill" data-bs-target="#revenue" type="button"
                                        role="tab">
                                        <i data-feather="dollar-sign" class="me-2"></i>
                                        Revenue Reports
                                    </button>
                                </li>
                            </ul>

                            <!-- Pills content -->
                            <div class="tab-content" id="reportsTabContent">
                                <!-- Revenue Reports -->
                                <div class="tab-pane fade <?= $activeTab === 'revenue' ? 'show active' : '' ?>"
                                    id="revenue" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <i data-feather="bar-chart-2" class="me-2"></i>
                                                        Revenue Summary
                                                    </h5>
                                                    <p class="card-text">Download a comprehensive revenue summary
                                                        report.</p>
                                                    <?= Html::a(
                                                        '<i data-feather="download" class="me-2"></i> Download Report',
                                                        ['reports/revenue-summary'],
                                                        ['class' => 'btn btn-primary']
                                                    ) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <i data-feather="trending-up" class="me-2"></i>
                                                        Monthly Revenue
                                                    </h5>
                                                    <p class="card-text">Generate monthly revenue analysis report.</p>
                                                    <?= Html::a(
                                                        '<i data-feather="download" class="me-2"></i> Download Report',
                                                        ['reports/monthly-revenue'],
                                                        ['class' => 'btn btn-primary']
                                                    ) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Reports -->
                                <div class="tab-pane fade <?= $activeTab === 'customers' ? 'show active' : '' ?>"
                                    id="customers" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <i data-feather="users" class="me-2"></i>
                                                        Customer List
                                                    </h5>
                                                    <p class="card-text">Download complete customer information report.
                                                    </p>
                                                    <?= Html::a(
                                                        '<i data-feather="download" class="me-2"></i> Download Report',
                                                        ['customers/report'],
                                                        ['class' => 'btn btn-primary']
                                                    ) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <i data-feather="activity" class="me-2"></i>
                                                        Customer Activity
                                                    </h5>
                                                    <p class="card-text">Generate customer activity and engagement
                                                        report.</p>
                                                    <?= Html::a(
                                                        '<i data-feather="download" class="me-2"></i> Download Report',
                                                        ['reports/customer-activity'],
                                                        ['class' => 'btn btn-primary']
                                                    ) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Requests Reports -->
                                <div class="tab-pane fade <?= $activeTab === 'requests' ? 'show active' : '' ?>"
                                    id="requests" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <i data-feather="shopping-cart" class="me-2"></i>
                                                        Request List
                                                    </h5>
                                                    <p class="card-text">Download complete customer requests report.</p>
                                                    <?= Html::a(
                                                        '<i data-feather="download" class="me-2"></i> Download Report',
                                                        ['customer-requests/report'],
                                                        ['class' => 'btn btn-primary']
                                                    ) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <i data-feather="pie-chart" class="me-2"></i>
                                                        Request Analytics
                                                    </h5>
                                                    <p class="card-text">Generate request trends and analytics report.
                                                    </p>
                                                    <?= Html::a(
                                                        '<i data-feather="download" class="me-2"></i> Download Report',
                                                        ['reports/request-analytics'],
                                                        ['class' => 'btn btn-primary']
                                                    ) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vendor Reports -->
                                <div class="tab-pane fade <?= $activeTab === 'vendors' ? 'show active' : '' ?>"
                                    id="vendors" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <i data-feather="truck" class="me-2"></i>
                                                        Vendor List
                                                    </h5>
                                                    <p class="card-text">Download complete vendor information report.
                                                    </p>
                                                    <?= Html::a(
                                                        '<i data-feather="download" class="me-2"></i> Download Report',
                                                        ['vendors/report'],
                                                        ['class' => 'btn btn-primary']
                                                    ) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <i data-feather="award" class="me-2"></i>
                                                        Vendor Performance
                                                    </h5>
                                                    <p class="card-text">Generate vendor performance and ratings report.
                                                    </p>
                                                    <?= Html::a(
                                                        '<i data-feather="download" class="me-2"></i> Download Report',
                                                        ['reports/vendor-performance'],
                                                        ['class' => 'btn btn-primary']
                                                    ) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
    // Initialize Feather icons
    if (typeof feather !== 'undefined') feather.replace();

    // Handle tab persistence
    const reportTabs = document.querySelectorAll('#reportTabs button');
    reportTabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', event => {
            const targetId = event.target.getAttribute('data-bs-target').replace('#', '');
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', targetId);
            window.history.pushState({}, '', newUrl);
        });
    });
JS;
$this->registerJs($js);
?>