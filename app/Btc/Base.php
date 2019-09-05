<?php

namespace App\Btc;

use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    public $accessKey;
    public $secretKey;

    public $huobiUrl = 'api.huobi.pro';
    public $protocl = 'https://';


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->accessKey = env('HUOBI_ACCESS_KEY');
        $this->secretKey = env('HUOBI_SECRET_KEY');
        date_default_timezone_set("PRC");
    }


    /**
     * @param $request
     * @param $params
     * @param string $method
     * @return string
     */
    public function getSign($requestMethod, $params = [], $method = 'GET')
    {
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['SignatureVersion'] = 2;
        $params['Timestamp'] = date('Y-m-d') . 'T' . date('H:i:s'); // 2017-05-11T15:19:30
        $params['AccessKeyId'] = $this->accessKey;
        ksort($params);
        $sign = strtoupper($method) . "\n";
        $sign .= strtolower($this->huobiUrl) . "\n";
        $sign .= strtolower($requestMethod) . "\n";

        foreach ($params as $key => $value) {
            $sign .= $key. '='. $value . '&';
        }
        $sign = rtrim($sign, '&');
        $sign = hash_hmac('sha256', $sign, $this->secretKey);
        return $sign;
    }



    /**
     * @param $url 请求网址
     * @param bool $params 请求参数
     * @param int $ispost 请求方式
     * @param int $https https协议
     * @return bool|mixed
     */
    public static function sendCurl($url, $params = false, $ispost = 0, $https = 1)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);

        if ($response === FALSE) {
            echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    /**
     * 组装url
     * @param $path
     * @return string
     */
    public function buildUrl($path)
    {
        return $this->protocl . $this->huobiUrl . '/' . $path;
    }
}
