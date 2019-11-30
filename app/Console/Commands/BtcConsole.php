<?php

namespace App\Console\Commands;

use App\Action\ActionOne;
use App\Btc;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class BtcConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'btc {cmd?}';

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

        // 获取账户信息
        $accountInfo = Cache::get('zqAccounts');
        if (empty($accountInfo)) {
            $oneObj = new Btc\Account();
            $accountInfo = $oneObj->getAccountAccounts();
            var_dump($accountInfo);
        }

        // 获取命令
        $command = $this->argument('cmd');

        if ($command == 'one') {
            $oneObj = new ActionOne();
            $oneObj->exec();
        } elseif ($command == 'gets') {
            echo '?s=' . md5('wangzhaoqistruggle' . strtotime('today')) . PHP_EOL;
            exit();
        } else {
            dd('try another comds');
        }

    }
}
