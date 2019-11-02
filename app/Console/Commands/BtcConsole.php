<?php

namespace App\Console\Commands;

use App\Action\ActionOne;
use App\Btc;
use Illuminate\Console\Command;

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
        $command = $this->argument('cmd');
        if($command == 'one'){
            $oneObj = new ActionOne();
            $oneObj->exec();
        }
    }
}
