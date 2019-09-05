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
        $params = $this->commonParams;
        $sign = $this->getSign($path, $params);
        $params['Signature'] = $sign;
        $params = array_merge($params, $this->commonParams);
//        $url = $this->buildUrl($path);
        $url = $this->protocl . $this->huobiUrl . $path;
        $result = self::sendCurl($url, $params);
        echo json_encode($result);
    }
}
