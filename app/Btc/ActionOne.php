<?php

namespace App\Btc;

use App\Db\Trade;

class ActionOne extends Base
{


    private $marketObj;
    private $tradeObj;


    public function __construct(array $attributes = [])
    {
        $this->marketObj = new Market();
        $this->tradeObj = new Trade();
        parent::__construct($attributes);
    }


    /**
     * 一小时内后半部分在平均值之上数量多的为上升趋势可以购入
     */
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

        $qualityData = [];
        foreach ($sortValue as $val) {
            $minuteData = $this->getTrendMinute($val['symbol']);

            // 求平均值
            $closePrice = array_column($minuteData, 'close');
            $average = $this->getAverage($closePrice);

            // 分为左右两个数组
            $chunkArr = array_chunk($closePrice, 10);

            // 判断是否为上升趋势
            if ($this->getAverage($chunkArr[0]) < $average && $this->getAverage($chunkArr[1]) > $average) {
                $qualityData[] = $val;
            }
        }

        if(!empty($qualityData)){
            $this->tradeObj->insertTrade([
                'symbol' => $qualityData[0]['symbol'],
                'buyPrice' => $qualityData[0]['close'],
            ]);
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


    /**
     * 分析近60分钟走势 20*5分
     * @param $symbol
     * @return mixed
     */
    public function getTrendMinute($symbol)
    {
        $inData['symbol'] = $symbol;
        $inData['period'] = '5min';
        $inData['size'] = 12;
        $return = $this->marketObj->kline($inData);
        $return = json_decode($return, true);
        return $return['data'];

    }






}
