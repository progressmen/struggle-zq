<?php

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


}

