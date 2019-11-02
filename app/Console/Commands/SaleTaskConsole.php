<?php

namespace App\Console\Commands;

use App\Btc\Market;
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
        $tradeData = $tradeObj->getTrade(['saleStatus' => 0]);

        if (count($tradeData) > 1) {
            $mailObj->warnMail('存在两笔或更多正在进行的任务');
            echo date('YmdHis') . ' ERROR TRADE' . PHP_EOL;
            return false;
        }


        if (empty($tradeData)) {
            echo date('YmdHis') . ' EMPTY TRADE' . PHP_EOL;
        } else {

            // 查询币种价格
            $marketObj = new Market();
            var_dump($tradeData[0]->symbol);
            $huobiRes = $marketObj->trade(['symbol' => $tradeData[0]->symbol]);
            var_dump($huobiRes);
//            echo $huobiRes;
//            echo PHP_EOL;
//            $huobiRes = json_decode($huobiRes, true);
//            if($huobiRes['status' == 'ok']){
//                $huobiData = $huobiRes['data'];
//            }
            exit;




            // 修改数据表状态
            // 开启事务
            DB::beginTransaction();

            // 更新任务表
            $taskRes = $taskObj->updateTask(['id' => $taskData[0]['id']], ['status' => 1]);

            if ($taskData[0]['type'] == 1) { // 买入
                $tradeUpdateData = ['buyStatus' => 1, 'buyEndTime' => time()];
            } else { // 卖出
                $tradeUpdateData = ['saleStatus' => 1, 'saleEndTime' => time()];
            }
            $tradeRes = $tradeObj->updateTrade(['id' => $taskData[0]['tradeId']],$tradeUpdateData);

            if ($tradeRes === false || $taskRes === false) {
                echo date('YmdHis') . ' DB ERROR TASK' . PHP_EOL;
                DB::rollBack();
            } else {
                $message = $taskData[0]['type'] == 1 ? '买入' : '卖出';
                $message = '交易id:' . $taskData[0]['tradeId'] . $message . '成功' . ' symbol:' . $tradeData[0]['symbol'];
                $mailObj->normalMail($message);
                echo date('YmdHis') . ' SUCCESS TASK' . PHP_EOL;
                DB::commit();
            }
        }
    }
}
