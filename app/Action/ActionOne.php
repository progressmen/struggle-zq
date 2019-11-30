<?php

namespace App\Action;

use App\Btc\Market;
use App\Db\Task;
use App\Db\Trade;
use App\Mail\CommonMail;
use Illuminate\Support\Facades\DB;

class ActionOne
{


    private $marketObj;
    private $tradeObj;
    private $taskObj;


    public function __construct()
    {
        $this->marketObj = new Market();
        $this->tradeObj = new Trade();
        $this->taskObj = new Task();
    }


    /**
     * 一小时内后半部分在平均值之上数量多的为上升趋势可以购入
     */
    public function exec()
    {

        // 检查当前有没有已购买的币
        $saleData = $this->tradeObj->getTrade(['saleStatus' => 0]);
        if (!empty($saleData)) {
            echo date('Y-m-d H:i:s') . 'ON TRADE' . PHP_EOL;
            return false;
        }

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

        // 如果小于5个则不购买
        if(count($sortValue) < 5){
            echo date('Y-m-d H:i:s') . 'LESS 5' . PHP_EOL;
            return false;
        }

        // 按交易量倒序排列
        $vol = array_column($sortValue, 'vol');
        array_multisort($vol, SORT_DESC, $sortValue);

        // 5分钟维度60分钟呈上升趋势
        $fiveQualityData = $this->getFiveMinuteQualityData($sortValue);

        // 1分钟维度10分钟呈上升趋势
        $qualityData = $this->getOneMinuteQualityData($fiveQualityData);

        if (!empty($qualityData)) {

            // 请求交易接口


            // 开启事务
            DB::beginTransaction();

            $money = 460;
            $amount = floatval($money/ $qualityData[0]['close']);
            // 插入交易记录
            $tradeRes = $this->tradeObj->insertTrade([
                'amount' => $amount,
                'symbol' => $qualityData[0]['symbol'],
                'buyPrice' => $qualityData[0]['close'],
            ]);

            // 插入任务表
            $taskRes = $this->taskObj->insertTask([
                'tradeId' => $tradeRes,
                'type' => 1,
            ]);

            if ($tradeRes === false || $taskRes === false) {
                DB::rollBack();
            } else {
                DB::commit();
                $mailObj = new CommonMail();
                $mailObj->normalMail('买单ID：'.$tradeRes.' 创建买单成功 symbol:' . $qualityData[0]['symbol']);
            }

            echo date('Y-m-d H:i:s') . $qualityData[0]['symbol'] . ' SUCCESS' . PHP_EOL;
        }

    }

    /**
     * 获取五分钟维度优质数据
     * @param $sortValue
     * @return array
     */
    public function getFiveMinuteQualityData($sortValue)
    {
        $qualityData = [];
        foreach ($sortValue as $val) {
            $minuteData = $this->getTrendFiveMinute($val['symbol']);
            if (empty($minuteData)) {
                continue;
            }
            echo 'getTrendFiveMinute:' . json_encode($minuteData) . PHP_EOL;

            // 求平均值
            $closePrice = array_column($minuteData, 'close');
            $average = $this->getAverage($closePrice);

            // 分为左右两个数组
            $chunkArr = array_chunk($closePrice, 6);

            // 判断是否为上升趋势
            if ($this->getAverage($chunkArr[0]) < $average && $this->getAverage($chunkArr[1]) > $average) {
                $qualityData[] = $val;
            }
        }
        return $qualityData;
    }

    /**
     * 获取一分钟维度优质数据
     * @param $sortValue
     * @return array
     */
    public function getOneMinuteQualityData($sortValue)
    {
        $qualityData = [];
        foreach ($sortValue as $val) {
            $minuteData = $this->getTrendOneMinute($val['symbol']);
            if (empty($minuteData)) {
                continue;
            }
//            echo 'getTrendOneMinute:' . json_encode($minuteData) . PHP_EOL;

            // 求平均值
            $closePrice = array_column($minuteData, 'close');
            $average = $this->getAverage($closePrice);

            // 分为左右两个数组
            $chunkArr = array_chunk($closePrice, 5);


            // 判断是否为上升趋势
            if ($this->getAverage($chunkArr[0]) < $average && $this->getAverage($chunkArr[1]) > $average) {
                $qualityData[] = $val;
            }
        }
        return $qualityData;
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
    public function getTrendFiveMinute($symbol)
    {
        $inData['symbol'] = $symbol;
        $inData['period'] = '5min';
        $inData['size'] = 12;
        $return = $this->marketObj->kline($inData);
        $return = json_decode($return, true);
        return $return['data'];

    }


    /**
     * 分析近60分钟走势 20*5分
     * @param $symbol
     * @return mixed
     */
    public function getTrendOneMinute($symbol)
    {
        $inData['symbol'] = $symbol;
        $inData['period'] = '1min';
        $inData['size'] = 10;
        $return = $this->marketObj->kline($inData);
        $return = json_decode($return, true);
        return $return['data'];

    }


}
