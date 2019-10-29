<?php

namespace App\Btc;

class Market extends Base
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
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
     * 聚合详情(此接口获取ticker信息同时提供最近24小时的交易聚合信息)
     * @param $params
     * symbol	string	true	NA	交易对	btcusdt, ethbtc
     */
    public function merged($params)
    {
        $path = 'market/detail/merged';
        $sign = $this->getSign($path, $params);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }

    /**
     * 所有交易对的最新 Tickers(获得所有交易对的 tickers，数据取值时间区间为24小时滚动。)
     */
    public function tickers()
    {
        $path = 'market/tickers';
        $sign = $this->getSign($path);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);

        //处理数据
        $result = json_decode($result, true);
        $output = [];
        if($result['status'] == 'ok'){
            $usdt = $btc = $eth = $ht = [];
            $time = date('YmdHis', intval($result['ts']/1000));
            foreach($result['data'] as $value){
                $value['close'] = sprintf("%.6f",$value['close']);
                $value['open']  = sprintf("%.6f",$value['open']);
                $value['high']  = sprintf("%.6f",$value['high']);
                $value['low']   = sprintf("%.6f",$value['low']);
                if (substr($value['symbol'], -4) == 'usdt') {
                    $sys = $value['close'] - $value['open'];
                    $value['percent'] = floatval(sprintf("%.2f",$sys/$value['open']*100));
                    $value['time'] = $time;
                    $usdt[] = $value;
                } elseif (substr($value['symbol'], -3) == 'btc') {
                    $sys = $value['close'] - $value['open'];
                    $value['percent'] = floatval(sprintf("%.2f",$sys/$value['open']*100));
                    $value['time'] = $time;
                    $btc[] = $value;
                } elseif (substr($value['symbol'], -3) == 'eth') {
                    $sys = $value['close'] - $value['open'];
                    $value['percent'] = floatval(sprintf("%.2f",$sys/$value['open']*100));
                    $value['time'] = $time;
                    $eth[] = $value;
                } elseif (substr($value['symbol'],  -2) == 'ht') {
                    $sys = $value['close'] - $value['open'];
                    $value['percent'] = floatval(sprintf("%.2f", $sys / $value['open'] * 100));
                    $value['time'] = $time;
                    $ht[] = $value;
                }
            }
            $percents = array_column($usdt,'percent');
            array_multisort($percents,SORT_DESC, $usdt);
            $output['usdt'] = $usdt;
            $percents = array_column($btc,'percent');
            array_multisort($percents,SORT_DESC, $btc);
            $output['btc'] = $btc;
            $percents = array_column($eth,'percent');
            array_multisort($percents,SORT_DESC, $eth);
            $output['eth'] = $eth;
            $percents = array_column($ht,'percent');
            array_multisort($percents,SORT_DESC, $ht);
            $output['ht'] = $ht;
        }
        return $output;
    }


    /**
     * 市场深度数据(此接口返回指定交易对的当前市场深度数据。)
     * @param $params
     * symbol	string	true	NA	交易对	btcusdt, ethbtc...
     * depth	integer	false	20	返回深度的数量	5，10，20
     * type	    string	true	step0	深度的价格聚合度，具体说明见下方	step0，step1，step2，step3，step4，step5
     */
    public function depth($params)
    {
        $path = 'market/depth';
        $sign = $this->getSign($path);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }


    /**
     * 最近市场成交记录(此接口返回指定交易对最新的一个交易记录。)
     * @param $params
     * symbol	string	true	NA	交易对，例如btcusdt, ethbtc
     */
    public function trade($params)
    {
        $path = 'market/trade';
        $sign = $this->getSign($path);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }

    /**
     * 获得近期交易记录(此接口返回指定交易对近期的所有交易记录)
     * @param $params
     * symbol	string	true	NA	交易对，例如 btcusdt, ethbtc
     * size	    integer	false	1	返回的交易记录数量，最大值2000
     */
    public function historyTrade($params)
    {
        $path = 'market/history/trade';
        $sign = $this->getSign($path);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }


    /**
     * 最近24小时行情数据(此接口返回最近24小时的行情数据汇总)
     * @param $params
     * symbol	string	true	NA	交易对，例如btcusdt, ethbtc
     */
    public function detail($params)
    {
        $path = 'market/detail';
        $sign = $this->getSign($path);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }











}
