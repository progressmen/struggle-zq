<?php

namespace App\Btc;

class Account extends HuobiBase
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }


    /**
     * 获取账户信息
     */
    function accounts() {
        $this->api_method = "/v1/account/accounts";
        $this->req_method = 'GET';
        $url = $this->create_sign_url([]);
        $result = $this->curl($url);
        echo json_encode($result);
    }
}
