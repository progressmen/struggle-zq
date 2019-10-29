<?php

namespace App\Btc;

class ActionOne extends Base
{

    private $marketObj;
    public function __construct(array $attributes = [])
    {
        $this->marketObj = new Market();
        parent::__construct($attributes);
    }


    public function exec()
    {
        // 获取当前涨幅排名
        $tickerData = $this->marketObj->tickers();
        $usdtData = $tickerData['usdt'];

        /*foreach ($usdtData as $value) {
            if($value['percent'] > 3){
                echo json_encode($value);
            }
        }*/

        $minuteData = $this->getTrendMinute($usdtData[0]['symbol']);
        $dayData = $this->getTrendDay($usdtData[0]['symbol']);
        $mouthData = $this->getTrendMonth($usdtData[0]['symbol']);

        echo json_encode($minuteData);
        echo PHP_EOL;
        echo json_encode($dayData);
        echo PHP_EOL;
        echo json_encode($mouthData);
        echo PHP_EOL;
        // 判断
    }

    // 分析近60分钟走势 20*5分
    public function getTrendMinute($symbol)
    {
        $inData['symbol'] = $symbol;
        $inData['period'] = '5min';
        $inData['size']   = 12;
        return $this->marketObj->kline($inData);

    }


    // 分析近一周走势  7*1天
    public function getTrendDay($symbol)
    {
        $inData['symbol'] = $symbol;
        $inData['period'] = '1day';
        $inData['size']   = 7;
        return $this->marketObj->kline($inData);
    }


    // 分析近半年走势  6*1月
    public function getTrendMonth($symbol)
    {
        $inData['symbol'] = $symbol;
        $inData['period'] = '1mon';
        $inData['size']   = 6;
        return $this->marketObj->kline($inData);
    }







}
