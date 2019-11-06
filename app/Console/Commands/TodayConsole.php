<?php

namespace App\Console\Commands;

use App\Db\Trade;
use App\Mail\CommonMail;
use Illuminate\Console\Command;

class TodayConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'today';

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
        $tradeObj = new Trade();
        $mailObj = new CommonMail();

        $today = strtotime('today');
        $tomorrow = strtotime('tomorrow');

        // 获取今天完成的单子
        $where = array(
            ['createTime', '>', $today],
            ['createTime', '<', $tomorrow],
            ['saleStatus', '=', 1]
        );
        $tradeData = $tradeObj->getTrade($where);
        $sum = 0;
        foreach ($tradeData as $value) {
            $sum += ($value->salePrice - $value->buyPrice) * $value->amount;
        }
        $symbols = array_column($tradeData,'symbol');
        $symbols = array_count_values($symbols);
        $symbolStr = '';
        foreach ($symbols as $k => $v){
            $symbolStr .= $k . ':' . $v . '次 ';
        }

        $message = '今天共交易:' . count($tradeData) . '笔' .PHP_EOL;
        $message .= '交易对:' . $symbolStr .PHP_EOL;
        $message .= '总收益:' . $sum .PHP_EOL;
        $mailObj->normalMail($message);
        echo date('YmdHis') . ' TODAY SUCCESS' . PHP_EOL;

    }
}
