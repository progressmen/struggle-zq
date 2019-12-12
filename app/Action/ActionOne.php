<?php

namespace App\Action;

use App\Btc\Account;
use App\Btc\Market;
use App\Btc\Orders;
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
            echo date('Y-m-d H:i:s') . ' ON THE TRADE' . PHP_EOL;
            return false;
        }

        // 获取当前涨幅排名
        $tickerData = $this->marketObj->tickers();
        $usdtData = $tickerData['usdt'];


        // 取出涨幅超过3%的数据
        $sortValue = [];
        foreach ($usdtData as $value) {
            if ($value['percent'] > 2 && $value['percent'] < 10) {
                $sortValue[] = $value;
            }
        }

        // 如果小于5个则不购买
        if (count($sortValue) < 5) {
            echo date('Y-m-d H:i:s') . ' MORE 2% LESS 5' . PHP_EOL;
            return false;
        }

        // 按交易量倒序排列
        $vol = array_column($sortValue, 'vol');
        array_multisort($vol, SORT_DESC, $sortValue);

        // 15分钟维度10组数据
        $fiveQualityData = $this->getQualityData($sortValue, '15min', 10);

        // 5分钟维度12组数据
        $qualityData = $this->getQualityData($fiveQualityData, '5min', 12);
        echo date('Y-m-d H:i:s') . ' qualityData : ' . json_encode($qualityData) . PHP_EOL;

        if (!empty($qualityData)) {

            // 请求交易接口
            $orderObj = new Orders();
            $accountObj = new Account();
            $accountInfo = $accountObj->getAccountAccounts();
            $account_id = $accountInfo['data'][0]['id'];
            $clientOrderId = 'st' . date('YmdHis');
            $money = 300;
            $amount = round(floatval($money / $qualityData[0]['close']), 2);
            $price = $qualityData[0]['close'];
            $symbol = $qualityData[0]['symbol'];
            $type = 'buy-market';
            // 市场价买入 amount字段传买入价格
            $placeRes = $orderObj->placeOrder($clientOrderId, $account_id, $money,  $symbol, $type);

            // 开启事务
            DB::beginTransaction();

            // 插入交易记录
            $tradeRes = $this->tradeObj->insertTrade([
                'amount' => $amount,
                'symbol' => $symbol,
                'buyPrice' => $price,
            ]);

            // 插入任务表

            $taskRes = $this->taskObj->insertTask([
                'clientOrderId' => $clientOrderId,
                'hbOrderId' => $placeRes['data'],
                'tradeId' => $tradeRes,
                'type' => 1,
            ]);


            if ($tradeRes === false || $taskRes === false || $placeRes['status'] != 'ok') {
                DB::rollBack();
                echo date('Y-m-d H:i:s') . $qualityData[0]['symbol'] . ' FAILED' . PHP_EOL;
            } else {
                DB::commit();
                $mailObj = new CommonMail();
                $mailObj->normalMail('买单ID：' . $tradeRes . ' 创建买单成功 symbol:' . $qualityData[0]['symbol'] . ' 买入价格：' . $qualityData[0]['close']);
                echo date('Y-m-d H:i:s') . $qualityData[0]['symbol'] . ' SUCCESS' . PHP_EOL;
            }


        }

    }


    /**
     * 获取上升趋势优质数据
     * @param $sortValue
     * @param string $period
     * @param int $size
     * @return array
     */
    public function getQualityData($sortValue, $period = '15min', $size = 10)
    {
        $qualityData = [];
        foreach ($sortValue as $val) {
            $minuteData = $this->getTrendMinute($val['symbol'], $period, $size);
            if (empty($minuteData)) {
                continue;
            }

            // 求平均值
            $closePrice = array_column($minuteData, 'close');
            $average = $this->getAverage($closePrice);

            // 分为左右两个数组
            $chunkArr = array_chunk($closePrice, $size / 2);

            // 判断是否为上升趋势
            if ($this->getAverage($chunkArr[0]) < $average && $this->getAverage($chunkArr[1]) > $average) {
                $qualityData[] = $val;
            }
        }
        return $qualityData;
    }


    /**
     * 获取kline数据
     * @param $symbol
     * @param string $period
     * @param int $size
     * @return mixed
     */
    public function getTrendMinute($symbol, $period = '15min', $size = 10)
    {
        $inData['symbol'] = $symbol;
        $inData['period'] = $period;
        $inData['size'] = $size;
        $return = $this->marketObj->kline($inData);
        $return = json_decode($return, true);
        return $return['data'];

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


}
