<?php

namespace App\Db;
use Illuminate\Support\Facades\DB;


class Trade
{
    const TABLE_NAME = 'b_trade';


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
        $data = DB::table(self::TABLE_NAME)->get();
        var_dump($data);
    }

    /**
     * 插入交易记录表
     * @param $data
     * @return bool
     */
    function insertTrade($data)
    {
        $addData = [
            'symbol',
            'buyPrice',
        ];

        if(!$this->checkEmpty($data, $addData)){
            return false;
        }

        $insertData = $this->makeData($data, $addData);
        $insertData['createTime'] = time();
        $insertData['buyStartTime'] = time();

        return DB::table(self::TABLE_NAME)->insertGetId($insertData);
    }


    /**
     * @param $data
     * @param $addData
     * @return bool
     */
    private function checkEmpty($data, $addData)
    {
        foreach ($addData as $val){
            if(!array_key_exists($val,$data)){
                return false;
            }
        }
        return true;
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
