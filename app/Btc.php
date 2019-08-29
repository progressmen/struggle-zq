<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Btc extends Model {


    private $accessKey;
    private $secretKey;

    private $huobiUrl = 'api.huobi.pro';
    private $protocl = 'https://';


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->accessKey = env('HUOBI_ACCESS_KEY');
        $this->secretKey = env('HUOBI_SECRET_KEY');
    }

    /**
     * 获取所有交易对
     * @return string
     */
    public function symbols()
    {
        $path = 'v1/common/symbols';
        $sign = $this->getSign($path);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }

    /**
     * 获取所有币种
     * @return string
     */
    public function currencys()
    {
        $path = 'v1/common/currencys';
        $sign = $this->getSign($path);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }


    /**
     * K 线数据（蜡烛图）
     * @param $params
     * symbol	string	true	NA	交易对	btcusdt, ethbtc...
     * period	string	true	NA	返回数据时间粒度，也就是每根蜡烛的时间区间	1min, 5min, 15min, 30min, 60min, 1day, 1mon, 1week, 1year
     * size	    integer	false	150	返回 K 线数据条数	[1, 2000]
     */
    public function kline($params)
    {
        $path = 'market/history/kline';
        $sign = $this->getSign($path, $params);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }

    /**
     * 获取账户信息
     */
    public function accounts()
    {
        $path = 'v1/account/accounts';
        $sign = $this->getSign($path);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }




    /********************** common private **********************/

    /**
     * @param $request
     * @param $params
     * @param string $method
     * @return string
     */
    private function getSign($requestMethod, $params = [], $method = 'GET')
    {
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
    private static function sendCurl($url, $params = false, $ispost = 0, $https = 1)
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
    private function buildUrl($path)
    {
        return $this->protocl . $this->huobiUrl . '/' . $path;
    }

}

