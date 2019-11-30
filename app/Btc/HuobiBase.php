<?php

namespace App\Btc;

use Illuminate\Database\Eloquent\Model;

class HuobiBase extends Model
{
    private $url = 'https://api.huobi.pro';
    private $api = '';
    public $api_method = '';
    public $req_method = '';

    private $accessKey = '';
    private $secretKey = '';


    public function __construct() {

        $this->api = parse_url($this->url)['host'];

        date_default_timezone_set("Etc/GMT+0");
//        date_default_timezone_set("PRC");
//        date_default_timezone_set("Asia/Shanghai");
        $this->accessKey = env('HUOBI_ACCESS_KEY');
        $this->secretKey = env('HUOBI_SECRET_KEY');

    }


    // 生成验签URL
    public function create_sign_url($append_param = []) {
        // 验签参数
        $param = [
            'AccessKeyId' => $this->accessKey,
            'SignatureMethod' => 'HmacSHA256',
            'SignatureVersion' => 2,
            'Timestamp' => date('Y-m-d\TH:i:s', time())
        ];
        if ($append_param) {
            foreach($append_param as $k=>$ap) {
                $param[$k] = $ap;
            }
        }
        return $this->url.$this->api_method.'?'.$this->bind_param($param);
    }

    // 组合参数
    public function bind_param($param) {
        $u = [];
        $sort_rank = [];
        foreach($param as $k=>$v) {
            $u[] = $k."=".urlencode($v);
            $sort_rank[] = ord($k);
        }
        asort($u);
        $u[] = "Signature=".urlencode($this->create_sig($u));
        return implode('&', $u);
    }

    // 生成签名
    public function create_sig($param) {
        $sign_param_1 = $this->req_method."\n".$this->api."\n".$this->api_method."\n".implode('&', $param);
        $signature = hash_hmac('sha256', $sign_param_1, $this->secretKey, true);
        return base64_encode($signature);
    }

    // 发送请求
    public function curl($url,$postdata=[]) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        if ($this->req_method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        }
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $output = json_decode($output,true);
        return $output;
    }
}
