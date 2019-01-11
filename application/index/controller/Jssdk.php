<?php
namespace app\index\controller;
use think\Cache;
use app\common\controller\Frontend;

class Jssdk extends Frontend
{
    private $appId;

    private $appSecret;

    public function __construct($appId, $appSecret)
    {

        $this->appId = $appId;

        $this->appSecret = $appSecret;

    }

    public function getSignPackage()
    {

        $jsapiTicket = $this->getJsApiTicket();

// 注意 URL 一定要动态获取，不能 hardcode.

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();

        $nonceStr = $this->createNonceStr();

// 这里参数的顺序要按照 key 值 ASCII 码升序排序

        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(

            "appId" => $this->appId,

            "nonceStr" => $nonceStr,

            "timestamp" => $timestamp,

            "url" => $url,

            "signature" => $signature,

            "rawString" => $string

        );

        return $signPackage;

    }

    private function createNonceStr($length = 16)
    {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        $str = "";

        for ($i = 0; $i < $length; $i++) {

            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);

        }

        return $str;

    }

    private function getJsApiTicket()
    {

        $data = Cache::get('wx_jsapi_ticket');

        if (!$data) {

            $ticket = $data;

        } else {

            $accessToken = $this->getAccessToken();

            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";

            $res = json_decode($this->httpGet($url));

            $ticket = $res->ticket;

            if ($ticket) {

                Cache::set('wx_jsapi_ticket', $ticket, 7000);

            }

        }

        return $ticket;
    }
    private function getAccessToken()
    {

        $data = Cache::get('wx_access_token');

        if ($data) {

            $access_token = $data;

        } else {

            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";

            $res = json_decode($this->httpGet($url));

            $access_token = $res->access_token;

            if ($access_token) {

                Cache::set('wx_access_token', $access_token, 7000);

            }

        }

        return $access_token;

    }
    private function httpGet($url)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_TIMEOUT, 500);

        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);

        curl_close($curl);

        return $res;

    }

}