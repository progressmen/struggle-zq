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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $taskObj = new Task();
        $tradeObj = new Trade();
        $mailObj = new CommonMail();
        $tradeData = $tradeObj->getTrade(['saleStatus' => 0, 'buyStatus' => 1]);

        if (count($tradeData) > 1) {
            $mailObj->warnMail('存在两笔或更多正在进行的任务');
            echo date('YmdHis') . ' ERROR TRADE' . PHP_EOL;
            return false;
        }


        if (empty($tradeData)) {
            echo date('YmdHis') . ' EMPTY TRADE' . PHP_EOL;
        } else {

            // 判断卖单是否存在
            $taskData = $taskObj->getTask(['tradeId' => $tradeData[0]->id,'type'=>2]);
            if(!empty($taskData)){
                echo date('YmdHis') . ' EXIST TASK' . PHP_EOL;
                return false;
            }

            // 查询币种价格
            $marketObj = new Market();
            $huobiRes = $marketObj->trade(['symbol' => $tradeData[0]->symbol]);
            $huobiRes = json_decode($huobiRes, true);

            if ($huobiRes['status'] == 'ok') {

                $huobiData = !empty($huobiRes['tick']['data']) ? $huobiRes['tick']['data'] : $huobiRes['data'];
                if(empty($huobiData)){
                    echo date('YmdHis') . ' EMPTY HUOBI RESULT' . PHP_EOL;
                    return false;
                }

                if ($huobiData[0]['price'] > $tradeData[0]->buyPrice * 1.04
                    || $huobiData[0]['price'] < $tradeData[0]->buyPrice * 0.97) {

                    // 创建卖单
                    $orderObj = new Orders();
                    $accountObj = new Account();

                    // 获取账户余额
                    $balanceData = $accountObj->getBalance();
                    if($balanceData['status'] != 'ok' || empty($balanceData['data']['list'])){
                        echo date('YmdHis') . ' GET BALANCE ERROR ' . PHP_EOL;
                        return false;
                    }

                    $amount = '';
                    foreach ($balanceData['data']['list'] as $val){
                        if($val['currency'] . 'usdt' == $tradeData[0]->symbol){
                            $amount = $val['balance'];
                        }
                    }

                    if(empty($amount)){
                        echo date('YmdHis') . ' GET AMOUNT ERROR ' . PHP_EOL;
                        return false;
                    }

                    $accountInfo = $accountObj->getAccountAccounts();
                    $account_id = $accountInfo['data'][0]['id'];
                    $clientOrderId = 'st' . date('YmdHis');
                    $amount = floor($amount * 100) / 100;
                    $price = $huobiData[0]['price'];
                    $symbol = $tradeData[0]->symbol;
                    $type = 'sell-limit';
                    $placeRes = $orderObj->placeOrder($clientOrderId, $account_id, $amount, $price, $symbol, $type);

                    // 开启事务
                    DB::beginTransaction();

                    $taskRes = $taskObj->insertTask([
                        'clientOrderId' =>  $clientOrderId,
                        'hbOrderId' => $placeRes['data'],
                        'tradeId' => $tradeData[0]->id,
                        'type' => 2,  // 卖出
                    ]);

                    $tradeUpdateData = [
                        'salePrice' => $huobiData[0]['price'],
                        'saleStartTime' => time()
                    ];
                    $tradeRes = $tradeObj->updateTrade(['id' => $tradeData[0]->id], $tradeUpdateData);

                    if ($tradeRes === false || $taskRes === false || $placeRes['status'] != 'ok') {
                        echo date('YmdHis') . ' DB ERROR TRADE' . PHP_EOL;
                        DB::rollBack();
                    } else {
                        $message = '交易id:' . $tradeData[0]->id .' 创建卖单成功' . ' symbol:' . $tradeData[0]->symbol;
                        $money = ($huobiData[0]['price'] - $tradeData[0]->buyPrice) * $tradeData[0]->amount;
                        $message .= ' 预计收益:' . $money;
                        $mailObj->normalMail($message);
                        echo date('YmdHis') . ' SUCCESS TRADE' . PHP_EOL;
                        DB::commit();
                    }
                } else {
                    echo date('YmdHis') . ' NO NEED HANDEL' . PHP_EOL;
                }
            }
        }
    }
}
