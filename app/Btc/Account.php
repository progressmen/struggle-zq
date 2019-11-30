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
        $zqAccounts = Cache::get('zqAccounts');
        if(!empty($zqAccounts)){
            var_dump($zqAccounts);
            return $zqAccounts;
        }

        $this->api_method = "/v1/account/accounts";
        $this->req_method = 'GET';
        $url = $this->create_sign_url([]);
        $result = $this->curl($url);
        Cache::put('zqAccounts', $result,24 * 3600 * 30);
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
        $accountData = json_decode($accountData, true);
        if ($accountData['status'] == 'ok') {
            foreach ($accountData['data'] as $value) {
                if($value['type'] == 'spot') {
                    $accountId = $value['id'];
                    $this->api_method = "/v1/account/accounts/{$accountId}/balance";
                    $this->req_method = 'GET';
                    $url = $this->create_sign_url([]);
                    $result = $this->curl($url);
                    $result = json_decode($result, true);
                    $newList = [];
                    foreach ($result['data']['list'] as $v){
                        if(!empty($v['balance'])) {
                            $newList[] = $v;
                        }
                    }
                    $result['data']['list'] = $newList;
                    echo json_encode($result);
                }
            }
        } else {
            echo '请求账户失败';
        }
    }
}
