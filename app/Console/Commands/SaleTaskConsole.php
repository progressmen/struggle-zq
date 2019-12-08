<?php

namespace App\Console\Commands;

use App\Btc\Account;
use App\Btc\Market;
use App\Btc\Orders;
use App\Db\Task;
use App\Db\Trade;
use App\Mail\CommonMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SaleTaskConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sale_task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查当前btc订单';

    private $taskObj;
    private $tradeObj;
    private $mailObj;
    private $marketObj;
    private $orderObj;
    private $accountObj;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->taskObj = new Task();
        $this->tradeObj = new Trade();
        $this->mailObj = new CommonMail();
        $this->marketObj = new Market();
        $this->orderObj = new Orders();
        $this->accountObj = new Account();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tradeData = $this->tradeObj->getTrade(['saleStatus' => 0, 'buyStatus' => 1]);

        if (count($tradeData) > 1) {
            $this->mailObj->warnMail('存在两笔或更多正在进行的任务');
            echo date('YmdHis') . ' ERROR TRADE' . PHP_EOL;
            return false;
        }


        if (empty($tradeData)) {
            echo date('YmdHis') . ' EMPTY TRADE' . PHP_EOL;
        } else {

            // 判断卖单是否存在
            $taskData = $this->taskObj->getTask(['tradeId' => $tradeData[0]->id, 'type' => 2]);
            if (!empty($taskData)) {
                echo date('YmdHis') . ' EXIST TASK' . PHP_EOL;
                return false;
            }

            // 查询币种价格
            $huobiRes = $this->marketObj->trade(['symbol' => $tradeData[0]->symbol]);
            $huobiRes = json_decode($huobiRes, true);

            if ($huobiRes['status'] == 'ok') {

                $huobiData = !empty($huobiRes['tick']['data']) ? $huobiRes['tick']['data'] : $huobiRes['data'];
                if (empty($huobiData)) {
                    echo date('YmdHis') . ' EMPTY HUOBI RESULT' . PHP_EOL;
                    return false;
                }

                // 手动卖出
                if ($tradeData[0]->isSale == 1) {
                    if (!empty($tradeData[0]->isSalePrice)) {
                        $type = 'sell-limit';
                        $price = $tradeData[0]->isSalePrice;
                    } else {
                        $type = 'sell-market';
                        $price = 0;
                    }

                    // 创建卖单 
                    $amount = $this->accountObj->getAcountBalance($tradeData[0]->symbol);
                    if ($amount === false) {
                        return $amount;
                    }
                    $accountInfo = $this->accountObj->getAccountAccounts();
                    $account_id = $accountInfo['data'][0]['id'];
                    $clientOrderId = 'st' . date('YmdHis');
                    $amount = floor($amount * 100) / 100;
                    $symbol = $tradeData[0]->symbol;
                    $placeRes = $this->orderObj->placeOrder($clientOrderId, $account_id, $amount, $symbol, $type, $price);

                    // 更新数据表
                    $this->updateTradeAndTask($clientOrderId, $placeRes, $tradeData, $huobiData);


                } elseif ( // 涨幅卖出
                    $huobiData[0]['price'] > $tradeData[0]->buyPrice * 1.04
                    || $huobiData[0]['price'] < $tradeData[0]->buyPrice * 0.97
                ) {

                    // 创建卖单
                    $amount = $this->accountObj->getAcountBalance($tradeData[0]->symbol);
                    if ($amount === false) {
                        return $amount;
                    }
                    $accountInfo = $this->accountObj->getAccountAccounts();
                    $account_id = $accountInfo['data'][0]['id'];
                    $clientOrderId = 'st' . date('YmdHis');
                    $amount = floor($amount * 100) / 100;
                    $symbol = $tradeData[0]->symbol;
                    $type = 'sell-market';
                    $placeRes = $this->orderObj->placeOrder($clientOrderId, $account_id, $amount, $symbol, $type);

                    // 更新数据表
                    $this->updateTradeAndTask($clientOrderId, $placeRes, $tradeData, $huobiData);

                } elseif ( // 有下降趋势
                    $huobiData[0]['price'] > $tradeData[0]->buyPrice
                    && $huobiData[0]['price'] < $tradeData[0]->tallPrice * 0.99
                ) {
                    // 创建卖单
                    $amount = $this->accountObj->getAcountBalance($tradeData[0]->symbol);
                    if ($amount === false) {
                        return $amount;
                    }
                    $accountInfo = $this->accountObj->getAccountAccounts();
                    $account_id = $accountInfo['data'][0]['id'];
                    $clientOrderId = 'st' . date('YmdHis');
                    $amount = floor($amount * 100) / 100;
                    $symbol = $tradeData[0]->symbol;
                    $type = 'sell-market';
                    $placeRes = $this->orderObj->placeOrder($clientOrderId, $account_id, $amount, $symbol, $type);

                    // 更新数据表
                    $this->updateTradeAndTask($clientOrderId, $placeRes, $tradeData, $huobiData);

                } else {
                    // 更新表最高价格
                    if ($huobiData[0]['price'] > $tradeData[0]->tallPrice) {
                        $tradeUpdateData = [
                            'tallPrice' => $huobiData[0]['price'],
                        ];
                        $this->tradeObj->updateTrade(['id' => $tradeData[0]->id], $tradeUpdateData);
                    }

                    echo date('YmdHis') . ' NO NEED HANDEL' . PHP_EOL;
                }
            }
        }
    }


    /**
     * 更新数据表
     * @param $clientOrderId
     * @param $placeRes
     * @param $tradeData
     * @param $huobiData
     */
    private function updateTradeAndTask($clientOrderId, $placeRes, $tradeData, $huobiData)
    {
        // 开启事务
        DB::beginTransaction();

        $taskRes = $this->taskObj->insertTask([
            'clientOrderId' => $clientOrderId,
            'hbOrderId' => $placeRes['data'],
            'tradeId' => $tradeData[0]->id,
            'type' => 2,  // 卖出
        ]);

        $tradeUpdateData = [
            'salePrice' => $huobiData[0]['price'],
            'saleStartTime' => time()
        ];
        $tradeRes = $this->tradeObj->updateTrade(['id' => $tradeData[0]->id], $tradeUpdateData);

        if ($tradeRes === false || $taskRes === false || $placeRes['status'] != 'ok') {
            echo date('YmdHis') . ' DB ERROR TRADE' . PHP_EOL;
            DB::rollBack();
        } else {
            $message = '交易id:' . $tradeData[0]->id . ' 创建卖单成功' . ' symbol:' . $tradeData[0]->symbol;
            $money = ($huobiData[0]['price'] - $tradeData[0]->buyPrice) * $tradeData[0]->amount;
            $message .= ' 预计收益:' . $money;
            $this->mailObj->normalMail($message);
            echo date('YmdHis') . ' SUCCESS TRADE' . PHP_EOL;
            DB::commit();
        }
    }
}
