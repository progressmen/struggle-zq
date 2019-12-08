<?php

namespace App\Btc;

use Illuminate\Support\Facades\Cache;

class Account extends HuobiBase
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }


    /**
     * 获取账户信息
     * https://huobiapi.github.io/docs/spot/v1/cn/#bd9157656f
     */
    function getAccountAccounts()
    {
        // $zqAccounts = Cache::forget('zqAccounts');dd($zqAccounts);
        $zqAccounts = Cache::get('zqAccounts');
        if(!empty($zqAccounts)){
            return $zqAccounts;
        }

        $this->api_method = "/v1/account/accounts";
        $this->req_method = 'GET';
        $url = $this->create_sign_url([]);
        $result = $this->curl($url);
        Cache::add('zqAccounts', $result,24 * 3600);
        return $result;
    }

    /**
     * 查询系统当前时间
     */
    function getCommonTimestamp()
    {
        $this->api_method = "/v1/common/timestamp";
        $this->req_method = 'GET';
        $url = $this->create_sign_url([]);
        $result = $this->curl($url);
        return $result;
    }

    /**
     * 获取账户余额
     * https://huobiapi.github.io/docs/spot/v1/cn/#870c0ab88b
     */
    function getBalance()
    {
        // 获取账户
        $accountData = $this->getAccountAccounts();
        if ($accountData['status'] == 'ok') {
            foreach ($accountData['data'] as $value) {
                if($value['type'] == 'spot') {
                    $accountId = $value['id'];
                    $this->api_method = "/v1/account/accounts/{$accountId}/balance";
                    $this->req_method = 'GET';
                    $url = $this->create_sign_url([]);
                    $result = $this->curl($url);
                    $newList = [];
                    foreach ($result['data']['list'] as $v){
                        if(!empty($v['balance'])) {
                            $newList[] = $v;
                        }
                    }
                    $result['data']['list'] = $newList;
                    return $result;
                }
            }
        } else {
            echo '请求账户失败';
        }
    }

    /**
     * 获取指定货币账户余额
     * @param $symbol
     * @return bool|string
     */
    public function getAcountBalance($symbol)
    {

        $balanceData = $this->getBalance();
        if ($balanceData['status'] != 'ok' || empty($balanceData['data']['list'])) {
            echo date('YmdHis') . ' GET BALANCE ERROR ' . PHP_EOL;
            return false;
        }

        $amount = false;
        foreach ($balanceData['data']['list'] as $val) {
            if ($val['currency'] . 'usdt' == $symbol) {
                $amount = $val['balance'];
            }
        }

        if (empty($amount)) {
            echo date('YmdHis') . ' GET AMOUNT ERROR ' . PHP_EOL;
            return false;
        }

        return $amount;
    }
}
