<?php

namespace common\components;

use Yii;
use yii\base\Component;

/**
 * Client for the HWA core billing APIs.
 *
 * - reading API: POST finalized clerk-approved readings
 *   (https://reading.hargeisawatertech.com/bill/api/meter-reading)
 * - meters API: meter-related validation/fetching
 *
 * Auth: static service JWT (Bearer), configured via .env:
 *   CORE_READING_API_URL, CORE_METERS_API_URL, CORE_API_TOKEN
 */
class CoreBillingClient extends Component
{
    public $readingApiUrl;
    public $metersApiUrl;
    public $token;
    public $timeout = 30;
    /** Set CORE_API_INSECURE_SSL=1 only while the reading API cert does not
     *  cover reading.hargeisawatertech.com. Remove once the cert is fixed. */
    public $insecureSsl = false;

    public function init()
    {
        parent::init();
        $this->readingApiUrl = $this->readingApiUrl ?: ($_ENV['CORE_READING_API_URL'] ?? '');
        $this->metersApiUrl = $this->metersApiUrl ?: ($_ENV['CORE_METERS_API_URL'] ?? '');
        $this->token = $this->token ?: ($_ENV['CORE_API_TOKEN'] ?? '');
        $this->insecureSsl = $this->insecureSsl || !empty($_ENV['CORE_API_INSECURE_SSL']);
    }

    public function isConfigured()
    {
        return !empty($this->readingApiUrl) && !empty($this->token);
    }

    /**
     * Push a finalized reading to the core billing server.
     * Payload matches the existing TB dashboard workflow:
     *   {date: 'Y-m-d', meterNo: <supply code>, reading: <m3, integer>}
     *
     * @return array{success:bool,reason:?string,message:string,http_code:int,body:mixed}
     */
    public function postMeterReading($supplyNo, $readingLiters, $date, $description = null)
    {
        $payload = [
            'date' => $date,
            'supplyno' => (string)$supplyNo,
            'reading' => (int)floor($readingLiters / 1000),
        ];
        if ($description !== null && $description !== '') {
            $payload['description'] = $description;
        }

        [$code, $raw] = $this->request('POST', rtrim($this->readingApiUrl, '/') . '/bill/api/meter-reading', $payload);
        $body = json_decode($raw, true);
        $text = strtolower($raw ?? '');

        $result = [
            'success' => $code >= 200 && $code < 300,
            'reason' => null,
            'message' => '',
            'http_code' => $code,
            'body' => $body ?: $raw,
        ];

        // Mirror the response interpretation used by the TB dashboard
        if (is_array($body)) {
            if (!empty($body['success'])) {
                $result['success'] = true;
                if (!empty($body['data']['already_posted'])) {
                    $result['reason'] = 'already_posted';
                    $result['message'] = $body['data']['message'] ?? 'Reading already posted for this supply.';
                    return $result;
                }
                $result['message'] = 'Successfully posted meter reading.';
                if (!empty($body['data']['invoice_number'])) {
                    $result['message'] .= ' Invoice: ' . $body['data']['invoice_number'];
                }
                return $result;
            }
            // JSON response without success flag = failure, even on HTTP 200
            $result['success'] = false;
            $result['message'] = $body['error'] ?? $body['message'] ?? 'Failed to post meter reading';
        } else {
            // Non-JSON response (e.g. server-side error text) = failure, even on HTTP 200
            $result['success'] = false;
            $result['message'] = $raw ?: ('HTTP ' . $code);
        }

        $msg = strtolower($result['message']) . ' ' . $text;
        if (strpos($msg, 'already') !== false && strpos($msg, 'posted') !== false) {
            $result['reason'] = 'already_posted';
        } elseif (strpos($msg, 'previous') !== false && strpos($msg, 'same') !== false) {
            $result['reason'] = 'same_reading';
        }

        return $result;
    }

    /**
     * Fetch meter data from the meters API (validation / profile lookups).
     */
    public function getMeter($meterNo)
    {
        if (empty($this->metersApiUrl)) {
            return null;
        }
        [$code, $raw] = $this->request('GET', rtrim($this->metersApiUrl, '/') . '/api/meters/' . rawurlencode($meterNo));
        return $code === 200 ? json_decode($raw, true) : null;
    }

    private function request($method, $url, $payload = null)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token,
            ],
        ]);
        if ($this->insecureSsl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }
        $response = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            Yii::error("Core API request failed: $method $url: $err", __METHOD__);
            return [0, $err];
        }
        return [$code, $response];
    }
}
