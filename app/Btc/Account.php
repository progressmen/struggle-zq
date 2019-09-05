<?php

namespace App\Btc;

class Account extends Base
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * 获取账户信息
     */
    public function accounts()
    {
        $path = '/v1/account/accounts';
        $sign = $this->getSign($path);
        $params['sign'] = $sign;
        $url = $this->buildUrl($path);
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }
}
