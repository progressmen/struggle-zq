<?php

namespace App\Btc;

class ActionOne extends Base
{

    private $marketObj;

    private $arrType = array(
        'type1', // 突然暴增
        'type2', // 稳步提升
        'type3', // 跌落之后提升
    );

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


        // 取出涨幅超过5%的数据
        $sortValue = [];
        foreach ($usdtData as $value) {
            if ($value['percent'] > 5) {
                $sortValue[] = $value;
            }
        }

        // 按交易量倒序排列
        $vol = array_column($sortValue, 'vol');
        array_multisort($vol, SORT_DESC, $sortValue);

        foreach ($sortValue as $val) {
            $minuteData = $this->getTrendMinute($val['symbol']);

            // 求平均值
            $closePrice = array_column($minuteData, 'close');
            $average = $this->getAverage($closePrice);

            // 分为左右两个数组
            $chunkArr = array_chunk($closePrice, 10);

            // 判断是否为上升趋势
            if ($this->getAverage($chunkArr[0]) < $average && $this->getAverage($chunkArr[1]) > $average) {
                echo $val['symbol'];
                echo "<br>";
            }

        }


    }

    /**
     * 获取平均值
     * @param $arr
     * @return float
     */
    private function getAverage($arr)
    {
        return array_sum($arr) / count($arr);
    }

    // 分析近60分钟走势 20*5分
    public function getTrendMinute($symbol)
    {
        $inData['symbol'] = $symbol;
        $inData['period'] = '5min';
        $inData['size'] = 12;
        $return = $this->marketObj->kline($inData);
        $return = json_decode($return, true);
        return $return['data'];

    }


    // 分析近一周走势  7*1天
    public function getTrendDay($symbol)
    {
        $inData['symbol'] = $symbol;
        $inData['period'] = '1day';
        $inData['size'] = 7;
        return $this->marketObj->kline($inData);
    }


    // 分析近半年走势  6*1月
    public function getTrendMonth($symbol)
    {
        $inData['symbol'] = $symbol;
        $inData['period'] = '1mon';
        $inData['size'] = 6;
        return $this->marketObj->kline($inData);
    }


}
