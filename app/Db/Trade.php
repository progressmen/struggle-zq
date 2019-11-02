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
        $this->insertTrade([
            'symbol' => 'asasdf',
            'buyPrice' => 111.00,
        ]);
    }


    function getTrade()
    {
        $data = DB::table('b_trade')->get();
        var_dump($data);
    }

    function insertTrade($data)
    {
        $addData = [
            'symbol',
            'buyPrice',
        ];

        $this->checkEmpty($data, $addData);

        $insertData = $this->makeData($data, $addData);
        $insertData['createTime'] = time();

        return DB::table('b_trade')->insert($insertData);
    }


    /**
     * @param $data
     * @param $addData
     */
    private function checkEmpty($data, $addData)
    {
        foreach ($addData as $val){
            if(!array_key_exists($val,$data)){
                throw new \Exception('参数错误',0);
            }
        }
    }

    /**
     * @param $data
     * @param $addData
     * @return array
     */
    private function makeData($data, $addData){
        $return = [];
        foreach ($addData as $val){
            $return[$val] = $data[$val];
        }

        return $return;
    }


}
