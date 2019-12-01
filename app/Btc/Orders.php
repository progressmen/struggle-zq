<?php

namespace App\Btc;

class Orders extends HuobiBase
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * 交易类API
     * @param string $type buy-market：市价买, sell-market：市价卖, buy-limit：限价买, sell-limit：限价卖, buy-ioc：IOC买单, sell-ioc：
     */
    // 下单
    function placeOrder($clientOrderId,$account_id=0,$amount=0,$price=0,$symbol='',$type='') {

        $source = 'api';
        $this->api_method = "/v1/order/orders/place";
        $this->req_method = 'POST';
        // 数据参数
        $postdata = [
            'account-id' => $account_id,
            'amount' => $amount,
            'source' => $source,
            'symbol' => $symbol,
            'type' => $type,
            'client-order-id' => $clientOrderId
        ];
        if ($price) {
            $postdata['price'] = $price;
        }
        $url = $this->create_sign_url();
        $return = $this->curl($url,$postdata);
        return $return;
    }


    /**
     * 获取账户信息
     * https://huobiapi.github.io/docs/spot/v1/cn/#bd9157656f
     */
    function getAccountAccounts()
    {
        $this->api_method = "/v1/account/accounts";
        $this->req_method = 'GET';
        $url = $this->create_sign_url([]);
        $result = $this->curl($url);
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
