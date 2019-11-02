<?php

namespace App\Db;
use Illuminate\support\Facades\DB;
use mysql_xdevapi\Exception;


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
                throw new Exception('参数错误');
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
