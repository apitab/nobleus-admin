<?php

namespace api\modules\v1\controllers;

//use yii\rest\ActiveController;
use yii\web\Controller;

class ApiController extends Controller
{

    const STATUS_OK = 200;
    const STATUS_ERROR = 500;

    /**
     * Set application headers
     * 
     * @param int status
     * @return null
     */
    public function setHeader($status)
    {

        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        $content_type = "application/json; charset=utf-8";

        header($status_header);
        header('Content-type: ' . $content_type);
        header('X-Powered-By: ' . "HWA");
    }

    /**
     * Return application status codes
     * 
     * @param int $status
     * @return string 
     */
    public function _getStatusCodeMessage($status)
    {
        $codes = array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }
}
