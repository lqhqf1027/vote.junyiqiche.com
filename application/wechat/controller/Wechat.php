<?php

namespace app\wechat\controller;

use think\request;
use think\Controller;
use think\Loader;
use think\Config;
use think\Db;
use think\Session;
use wechat\Wx;
use app\admin\model\Wechatuser;

class Wechat extends Controller
{

    /**
     * 获取用户信息插入数据库
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    protected $model = '';
    //微信授权配置信息
    private $Wxapis;

    public function __construct()
    {
        $this->Wxapis = new Wx(Config::get('oauth')['appid'], Config::get('oauth')['appsecret']);
    }

    public function getCodes()
    {
        $this->Wxapis->getWxUser('https://vote.junyiqiche.com/wechat/Wechat/adduser');
    }

    /**
     * 获取秘钥配置
     * @return [type] 数组
     */
    public function wechatConfig()
    {
        $wechat_config = array_merge($this->wechat_config, config('oauth'));
        return $wechat_config;
    }

    /**
     * Notes:获取用户信息插入数据库
     * User: glen9
     * Date: 2019/1/15
     * Time: 23:33
     * @throws \think\exception\DbException
     */
    public function adduser()
    {
        $this->model = model('app\admin\model\Wechatuser');
        if (\session('wx_state') != $_GET['state']) {
            $this->getCodes();
            die();
        }
        $userInfo = $this->Wxapis->WxUserInfo($_GET['code']);
        $userInfo['nickname'] = emoji_encode($userInfo['nickname']); //昵称转义
        $user_data = Wechatuser::get(['openid' => $userInfo['openid']]);
        $user_data = $user_data ? $user_data->getData() : '';
        unset($userInfo['privilege']);
        if (empty($user_data)) {
            $res = Wechatuser::create($userInfo)->getData();
            if ($res) {
                $res['nickname'] = emoji_decode($res['nickname']);//表情解密,存入session
                Session::set('MEMBER', $res);
                $this->redirect('Index/index/index');
            } else {
                die('<h1 class="text-center">用户新增失败</h1>');
            }
        } else {
            if (strcmp($user_data['nickname'], $userInfo['nickname']) != 0 || strcmp($user_data['headimgurl'], $userInfo['headimgurl']) != 0) {
                $user_data['nickname'] = $userInfo['nickname'];
                $user_data['headimgurl'] = $userInfo['headimgurl'];
                // 更新当前用户信息
                Wechatuser::update($user_data);
            }
            $user_data['nickname'] = emoji_decode($user_data['nickname']);//表情解密,存入session
            Session::set('MEMBER', $user_data);
            $this->redirect('Index/index/index');

        }
    }

    // 用于请求微信接口获取数据
    public function get_by_curl($url, $post = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
