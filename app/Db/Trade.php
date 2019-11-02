<?php

namespace App\Db;
use Illuminate\support\Facades\DB;


class Trade
{
    public function __construct(array $attributes = [])
    {

    }



    function exec()
    {
        $data = DB::table('b_trade')->get();
        var_dump($data);
    }


    function getTrade()
    {
        $data = DB::table('b_trade')->get();
        var_dump($data);
    }


}
