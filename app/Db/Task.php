<?php

namespace App\Db;
use Illuminate\Support\Facades\DB;


class Task
{
    const TABLE_NAME = 'b_task';



    /**
     * @param $where
     * @param $field
     * @return array
     */
    function getTask($where, $field = '*')
    {
        return DB::table(self::TABLE_NAME)->select($field)->where($where)->get()->toArray();
    }



    /**
     * 插入交易记录表
     * @param $data
     * @return bool
     */
    function insertTask($data)
    {
        $addData = [
            'tradeId',
            'type',
        ];

        if(!$this->checkEmpty($data, $addData)){
            return false;
        }

        $insertData = $this->makeData($data, $addData);
        $insertData['createTime'] = time();

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
