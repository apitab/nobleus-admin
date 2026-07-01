<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class FrontAppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'lib/bootstrap/css/bootstrap.min.css',
        'lib/@fortawesome/fontawesome-free/css/all.min.css',
        'css/site.css'
    ];
    public $js = [
        'lib/bootstrap/js/bootstrap.bundle.min.js',
        'lib/jquery/jquery.min.js',
        'lib/jqueryui/jquery-ui.min.js',
        'lib/feather-icons/feather.min.js',
        'js/app.js',
    ];
    public $depends = [
        
    ];
    public $jsOptions = array(
        'position' => \yii\web\View::POS_HEAD
    );
}
