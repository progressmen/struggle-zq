<?php

namespace App\Db;
use Illuminate\Support\Facades\DB;


class Trade
{
    const TABLE_NAME = 'b_trade';



    /**
     * @param $where
     * @param string $field
     * @return array
     */
    function getTrade($where, $field = '*')
    {
        return DB::table(self::TABLE_NAME)->select($field)->where($where)->get()->toArray();
    }

    /**
     * 插入交易记录表
     * @param $data
     * @return bool
     */
    function insertTrade($data)
    {
        $addData = [
            'amount',
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
     * 更新数据库
     * @param $where
     * @param $updateData
     * @return int
     */
    function updateTrade($where, $updateData)
    {
        return DB::table(self::TABLE_NAME)->where($where)->update($updateData);
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
