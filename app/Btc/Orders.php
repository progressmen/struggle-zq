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
     * @param $clientOrderId
     * @param int $account_id
     * @param int $amount 市价购买此字段为交易额
     * @param int $price string
     * @param string $symbol
     * @param string $type  buy-market：市价买, sell-market：市价卖, buy-limit：限价买, sell-limit：限价卖, buy-ioc：IOC买单, sell-ioc：
     * @return bool|mixed|string
     */
    // 下单
    function placeOrder($clientOrderId,$account_id=0,$amount=0,$symbol='',$type='',$price=0) {

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
        echo '下单数据: input: ' . json_encode($postdata) . ' output: ' . json_encode($return) . PHP_EOL;
        return $return;
    }


    // 申请撤销一个订单请求
    function cancelOrder($order_id) {
        $this->api_method = '/v1/order/orders/'.$order_id.'/submitcancel';
        $this->req_method = 'POST';
        $postdata = [];
        $url = $this->create_sign_url();
        $return = $this->curl($url,$postdata);
        return $return;
    }


    // 查询某个订单详情
    function getOrder($order_id) {
        $this->api_method = '/v1/order/orders/'.$order_id;
        $this->req_method = 'GET';
        $url = $this->create_sign_url();
        $return = $this->curl($url);
        return $return;
    }



}
