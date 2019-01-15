<?php

namespace app\wechat\controller;

use think\request;
use think\Controller;
use think\Loader;
use think\Config;
use think\Db;
use think\Session;
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
    private $wechat_config = [
        'appid' => '',
        'appsecret' => '',
    ];

    public function __construct()
    {
        $this->wechat_config = $this->wechatConfig();
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

    /*获取用户信息插入数据库*/
    public function adduser()
    {
        // 获取页面URL的CODE参数，判断是否有值
        if (isset($_GET['code'])) {
            // 获取openid和access_token
            $code = $_GET['code'];
            // 发送请求，获取用户openid和access_token
            $data = $this->get_by_curl('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->wechat_config['appid'] . '&secret=' . $this->wechat_config['appsecret'] . '&code=' . $code . '&grant_type=authorization_code');
            $data = json_decode($data);

            // 防止第二次访问动态链接报错
            // 判断是否获取到当前用户的openid
            if (isset($data->openid)) {

                $open_id = $data->openid;
                $access_token = $data->access_token;

                // 获取当前用户信息
                $user_info = $this->get_by_curl('https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $open_id . '&lang=zh_CN');
                $user_info = json_decode($user_info, true);
                $user_info['nickname'] = htmlspecialchars($user_info['nickname']);
                unset($user_info['privilege']);
                // 判断用户是否存在
                $data_user = Db::name('wechat_user')->where(['openid' => $user_info['openid']])->find();
                if (empty($data_user)) {

                    // 新增用户到数据库
                    $insertsId = Db::name('wechat_user')->insertGetId($user_info);
                    if ($insertsId) {
                        $res = self::getUser($insertsId);
                        Session::set('MEMBER', $res);
                        $this->redirect('Index/index/index');
                    } else {
                        die('<h1 class="text-center">用户新增失败</h1>');
                    }
                } else {


                    // 判断当前用户是否修改过信息,若有变动则更新
                    if (strcmp($data_user['nickname'], $user_info['nickname']) != 0 || strcmp($data_user['headimgurl'], $user_info['headimgurl']) != 0) {

                        $data_user['nickname'] = $user_info['nickname'];
                        $data_user['headimgurl'] = $user_info['headimgurl'];
                        // 更新当前用户信息
                        Db::name('wechat_user')->update($data_user);
                    }
                    Session::set('MEMBER', $data_user);
                    $this->redirect('Index/index/index');

                }


            }
            die('<h1 class="text-center">获取用户信息失败</h1>');

        }
    }

    public static function getUser($userId)
    {
        return Db::name('wechat_user')->where(['id' => $userId])->find();
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
