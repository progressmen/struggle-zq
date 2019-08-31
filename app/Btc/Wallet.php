<?php

namespace App\Btc;

class Wallet extends Base
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * 获取账户信息
     * @param $params array
     * currency    false    string    币种        缺省时，返回所有币种
     * type        true    string    充值或提现        deposit 或 withdraw
     * from        false    string    查询起始 ID    缺省时，默认值direct相关。当direct为‘prev’时，from 为1 ，从旧到新升序返回；当direct为’next‘时，from为最新的一条记录的ID，从新到旧降序返回
     * size        false    string    查询记录大小    100    1-500
     * direct    false    string    返回记录排序方向    缺省时，默认为“prev” （升序）    “prev” （升序）or “next” （降序）
     */
    public function depositAndWithdraw($params = [])
    {
        if (!isset($params['type'])) {
            $params['type'] = 'deposit';
        }
        $path = 'v1/query/deposit-withdraw';
        $sign = $this->getSign($path, $params);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }
}
