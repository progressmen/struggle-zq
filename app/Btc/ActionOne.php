<?php

namespace App\Btc;

class ActionOne extends Base
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }


    public function exec()
    {
        // 获取当前涨幅排名
        $marketObj = new Market();
        $tickerData = $marketObj->tickers();

        echo json_encode($tickerData['usdt']);

        // 判断
    }






}
