<?php

use backend\helpers\Helpers;
use common\helpers\ViewHelper;

/** @var yii\web\View $this */
$this->title = "Dashboard | Demo";
$hourOfDay = Helpers::getTimeOfDay();
?>
<div class="content pd-t-20">
  <?= $this->render('//layouts/dashboard/_breadcrumbs', [
    'title' => '<i data-feather="smile" class="wd-20 ht-20 stroke-2 mg-r-5"></i>' . $hourOfDay . ' ' . Yii::$app->user->identity->names . '!',
    'links' => [
      ['title' => Yii::t('app','Dashboard'), 'active' => true]
    ],
  ]); ?>
  <?= ViewHelper::displayFlash(); ?>

  <!-- Key Metrics Section -->
  <div class="row row-xs mg-b-25">
    <div class="col-sm-6 col-lg-2">
      <div class="card card-body">
        <div class="d-flex align-items-center mg-b-10">
          <i data-feather="users" class="wd-30 ht-30 stroke-2 text-secondary"></i>
          <span class="tx-color-03 tx-12 mg-l-10"><?= Yii::t('app','Vendors') ?></span>
        </div>
        <h2 class="tx-normal mg-b-2 tx-spacing--1"><?= number_format($vendors_count) ?></h2>
        <small class="tx-11 tx-color-03"><?= Yii::t('app','Total registered vendors') ?></small>
      </div>
    </div>
    <div class="col-sm-6 col-lg-2">
      <div class="card card-body">
        <div class="d-flex align-items-center mg-b-10">
          <i data-feather="home" class="wd-30 ht-30 stroke-2 text-info"></i>
          <span class="tx-color-03 tx-12 mg-l-10"><?= Yii::t('app','Kiosks') ?></span>
        </div>
        <h2 class="tx-normal mg-b-2 tx-spacing--1"><?= number_format($kiosks_count) ?></h2>
        <small class="tx-11 tx-color-03"><?= Yii::t('app','Active water points') ?></small>
      </div>
    </div>
    <div class="col-sm-6 col-lg-2">
      <div class="card card-body">
        <div class="d-flex align-items-center mg-b-10">
          <i data-feather="user" class="wd-30 ht-30 stroke-2 text-primary"></i>
          <span class="tx-color-03 tx-12 mg-l-10"><?= Yii::t('app','Customers') ?></span>
        </div>
        <h2 class="tx-normal mg-b-2 tx-spacing--1"><?= number_format($customers_count) ?></h2>
        <small class="tx-11 tx-color-03"><?= Yii::t('app','Registered users') ?></small>
      </div>
    </div>
    <div class="col-sm-6 col-lg-2">
      <div class="card card-body">
        <div class="d-flex align-items-center mg-b-10">
          <i data-feather="shopping-cart" class="wd-30 ht-30 stroke-2 text-success"></i>
          <span class="tx-color-03 tx-12 mg-l-10"><?= Yii::t('app','Orders') ?></span>
        </div>
        <h2 class="tx-normal mg-b-2 tx-spacing--1"><?= number_format($customer_requests) ?></h2>
        <small class="tx-11 tx-color-03"><?= Yii::t('app','Total orders placed') ?></small>
      </div>
    </div>
    <div class="col-sm-6 col-lg-2">
      <div class="card card-body">
        <div class="d-flex align-items-center mg-b-10">
          <i data-feather="dollar-sign" class="wd-30 ht-30 stroke-2 text-warning"></i>
          <span class="tx-color-03 tx-12 mg-l-10"><?= Yii::t('app','Avg. Price') ?></span>
        </div>
        <h2 class="tx-normal mg-b-2 tx-spacing--1"><?= Helpers::localCurrencyFormatter($average_request_amount) ?></h2>
        <small class="tx-11 tx-color-03"><?= Yii::t('app','Per order') ?></small>
      </div>
    </div>
    <div class="col-sm-6 col-lg-2">
      <div class="card card-body">
        <div class="d-flex align-items-center mg-b-10">
          <i data-feather="droplet" class="wd-30 ht-30 stroke-2 text-danger"></i>
          <span class="tx-color-03 tx-12 mg-l-10"><?= Yii::t('app','Avg. Volume') ?></span>
        </div>
        <h2 class="tx-normal mg-b-2 tx-spacing--1"><?= number_format($average_request_volume) ?></h2>
        <small class="tx-11 tx-color-03"><?= Yii::t('app','Litres per order') ?></small>
      </div>
    </div>
  </div>

  <!-- Maps Section -->
  <div class="row row-xs mg-b-25">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header pd-t-15 pd-b-15">
          <h6 class="mg-b-0">
            <i data-feather="map-pin" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
            <?= Yii::t('app','Vendors Distribution') ?>
          </h6>
          <span class="badge badge-primary tx-12 mg-l-10"><?= count($vendorLocations) ?> <?= Yii::t('app','locations') ?></span>
        </div>
        <div class="card-body pd-0">
          <div id="dashboard-vendor-map" style="height: 300px"></div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header pd-t-15 pd-b-15">
          <h6 class="mg-b-0">
            <i data-feather="home" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
            <?= Yii::t('app','Kiosks Distribution') ?>
          </h6>
          <span class="badge badge-primary tx-12 mg-l-10"><?= count($kiosksLocations) ?> <?= Yii::t('app','kiosks') ?></span>
        </div>
        <div class="card-body pd-0">
          <div id="dashboard-kiosks-map" style="height: 300px"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activity Section -->
  <div class="row row-xs">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header pd-t-15 pd-b-15">
          <h6 class="mg-b-0">
            <i data-feather="truck" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
            <?= Yii::t('app','Recently Listed Vendors') ?>
          </h6>
        </div>
        <div class="card-body pd-0">
          <ul class="list-group list-group-flush">
            <?php foreach ($vendors as $vendor): ?>
              <li class="list-group-item d-flex align-items-center pd-15">
                <div class="avatar">
                  <span class="avatar-initial rounded-circle bg-primary"><?= substr($vendor->first_name, 0, 1) ?></span>
                </div>
                <div class="pd-l-15">
                  <h6 class="tx-13 mg-b-5"><?= $vendor->other_names . ', ' . $vendor->first_name; ?></h6>
                  <span class="tx-12 tx-color-03">
                    <i data-feather="phone" class="wd-12 ht-12 stroke-2 mg-r-5"></i>
                    <?= $vendor->mobile_number; ?>
                  </span>
                </div>
                <div class="mg-l-auto">
                  <a href="<?= Yii::$app->urlManager->createUrl(['vendors/view', 'id' => $vendor->id]) ?>" 
                     class="btn btn-sm btn-white" 
                     data-toggle="tooltip" 
                     title="<?= Yii::t('app', 'View Details') ?>">
                    <i data-feather="eye" class="wd-15 ht-15 stroke-2"></i>
                  </a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="card-footer text-center">
          <a href="<?= Yii::$app->urlManager->createUrl('vendors') ?>" class="btn btn-sm btn-outline-primary">
            <?= Yii::t('app','View All Vendors') ?>
            <i data-feather="chevron-right" class="wd-15 ht-15 stroke-2 mg-l-5"></i>
          </a>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header pd-t-15 pd-b-15">
          <h6 class="mg-b-0">
            <i data-feather="users" class="wd-15 ht-15 stroke-2 mg-r-5"></i>
            <?= Yii::t('app','Recent Registered Customers') ?>
          </h6>
        </div>
        <div class="card-body pd-0">
          <ul class="list-group list-group-flush">
            <?php foreach ($customers as $customer): ?>
              <li class="list-group-item d-flex align-items-center pd-15">
                <div class="avatar">
                  <span class="avatar-initial rounded-circle bg-secondary"><?= substr($customer->alias, 0, 1) ?></span>
                </div>
                <div class="pd-l-15">
                  <h6 class="tx-13 mg-b-5"><?= $customer->alias; ?></h6>
                  <span class="tx-12 tx-color-03">
                    <i data-feather="phone" class="wd-12 ht-12 stroke-2 mg-r-5"></i>
                    <?= $customer->phone_number; ?>
                  </span>
                </div>
                <div class="mg-l-auto">
                  <a href="<?= Yii::$app->urlManager->createUrl(['customers/view', 'id' => $customer->id]) ?>" 
                     class="btn btn-sm btn-white" 
                     data-toggle="tooltip" 
                     title="<?= Yii::t('app', 'View Profile') ?>">
                    <i data-feather="eye" class="wd-15 ht-15 stroke-2"></i>
                  </a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="card-footer text-center">
          <a href="<?= Yii::$app->urlManager->createUrl('customers') ?>" class="btn btn-sm btn-outline-primary">
            <?= Yii::t('app','View All Customers') ?>
            <i data-feather="chevron-right" class="wd-15 ht-15 stroke-2 mg-l-5"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Maps initialization
    const locations = [
        <?php foreach ($vendorLocations as $location): ?>
            ["<b><?= $location['other_names'] . ', ' .  $location['first_name'] ?></b>", <?= $location['location_coordinates'] ?>],
        <?php endforeach; ?>
    ];

    const kiosks = [
        <?php foreach ($kiosksLocations as $kiosk): ?>
            ["<b><?= $kiosk['name'] ?></b>", <?= $kiosk['location_coordinates'] ?>],
        <?php endforeach; ?>
    ];

    function initMaps() {
        // Vendor Map
        if (locations.length > 0) {
            const vendorMap = L.map('dashboard-vendor-map').setView([locations[0][1], locations[0][2]], 13);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(vendorMap);

            locations.forEach(location => {
                L.marker([location[1], location[2]])
                    .bindPopup(location[0])
                    .addTo(vendorMap);
            });
        }

        // Kiosks Map
        if (kiosks.length > 0) {
            const kiosksMap = L.map('dashboard-kiosks-map').setView([kiosks[0][1], kiosks[0][2]], 13);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(kiosksMap);

            kiosks.forEach(kiosk => {
                L.marker([kiosk[1], kiosk[2]])
                    .bindPopup(kiosk[0])
                    .addTo(kiosksMap);
            });
        }
    }

    initMaps();
});
</script>