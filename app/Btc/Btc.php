<?php
/**
 * 基础信息相关
 */
namespace App\Btc;


class Btc extends Base {


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
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
     * 获取当前系统时间
     * 此接口返回当前的系统时间，时间是调整为北京时间的时间戳，单位毫秒。
     */
    public function getTimestamp()
    {
        $path = 'v1/common/timestamp';
        $sign = $this->getSign($path);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }





}

