<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'lib/leaflet/leaflet.css',
        'lib/@fortawesome/fontawesome-free/css/all.min.css',
        'css/dashforge.css',
        'css/dashforge.dashboard.css',
    ];
    public $js = [
        'lib/leaflet/leaflet.js',
        'lib/jquery/jquery.min.js',
        'lib/bootstrap/js/bootstrap.bundle.min.js',
        'lib/feather-icons/feather.min.js',
        //'lib/perfect-scrollbar/perfect-scrollbar.min.js',
        'lib/jqueryui/jquery-ui.min.js',
        'js/app.js',
    ];
    public $depends = [
        
    ];
    public $jsOptions = array(
        'position' => \yii\web\View::POS_HEAD
    );
}
