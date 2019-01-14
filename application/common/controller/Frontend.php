<?php

namespace app\common\controller;

use app\common\library\Auth;
use think\Config;
use think\Controller;
use think\Hook;
use think\Lang;
use app\common\model\Config as ConfigModel;
use think\Session;

/**
 * 前台控制器基类
 */
class Frontend extends Controller
{

    /**
     * 布局模板
     * @var string
     */
    protected $layout = '';

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 权限Auth
     * @var Auth
     */
    protected $auth = null;
    /**
     * 投票用户id
     * @var null
     */
    protected $user_id = null;

    public function _initialize()
    {

        //微信登陆验证
        $appid = $this->appid = Config::get('APPID');
        $secret = $this->secret = Config::get('APPSECRET');
        $token = cache('Token');


        /*if(!$token['access_token'] || $token['expires_in'] <= time()){


            $rslt  = gets("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}");
            if($rslt){
                $accessArr = array(
                    'access_token'=>$rslt['access_token'],
                    'expires_in'=>time()+$rslt['expires_in']-200
                );
                cache('Token',$accessArr) ;
                $token = $rslt;
            }
        }
        if(!session('MEMBER')){

            ##没有登录
            ##如果没有登录，我们要让url地址跳转到 微信url 去获取code
            $myurl =  urlencode('https://yinchuan.junyiqiche.com/wechat/Wechat/adduser');//mvc : http://wx4.cdphm.net/User/wxlogin  ##微信回调地址（这个地址是我们自己的一个url地址，必须使用urlencode处理）
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appid}&redirect_uri={$myurl}&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
            header('Location:'.$url);
            die();
        }*/
        //移除HTML标签
        $this->request->filter('strip_tags');
        $modulename = $this->request->module();
        $controllername = strtolower($this->request->controller());
        $actionname = strtolower($this->request->action());


        // 如果有使用模板布局
        if ($this->layout) {
            $this->view->engine->layout('layout/' . $this->layout);
        }
        $this->auth = Auth::instance();

        // token
        $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', \think\Cookie::get('token')));

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;
        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin)) {
            //初始化
            $this->auth->init($token);
            //检测是否登录
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'), 'user/login');
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法判断是否有对应权限
                if (!$this->auth->check($path)) {
                    $this->error(__('You have no permission'));
                }
            }
        } else {
            // 如果有传递token才验证是否登录状态
            if ($token) {
                $this->auth->init($token);
            }
        }

        $this->view->assign('user', $this->auth->getUser());

        // 语言检测
        $lang = strip_tags($this->request->langset());

        $site = Config::get("site");

        $upload = \app\common\model\Config::upload();

        // 上传信息配置后
        Hook::listen("upload_config_init", $upload);

        // 配置信息
        $config = [
            'site' => array_intersect_key($site, array_flip(['name', 'cdnurl', 'version', 'timezone', 'languages'])),
            'upload' => $upload,
            'modulename' => $modulename,
            'controllername' => $controllername,
            'actionname' => $actionname,
            'jsname' => 'frontend/' . str_replace('.', '/', $controllername),
            'moduleurl' => rtrim(url("/{$modulename}", '', false), '/'),
            'language' => $lang
        ];
        $config = array_merge($config, Config::get("view_replace_str"));

        Config::set('upload', array_merge(Config::get('upload'), $upload));

        // 配置信息后
        Hook::listen("config_init", $config);
        // 加载当前控制器语言包
        $this->loadlang($controllername);
        $this->assign('site', $site);
        $this->assign('config', $config);
        $user_id = session('MEMBER');
        $this->user_id = $user_id ? $user_id->getData()['id'] : 0;
        $this->assign('user_id', $this->user_id);

        //卡片分享参数
        $shareData = ConfigModel::where('name', ['eq', 'share_title'], ['eq', 'share_body'], ['eq', 'share_img'], 'or')->column('value');
        $this->assign(['share_title' => $shareData[2], 'share_body' => $shareData[0], 'share_img' => Config::get('upload')['cdnurl'] . $shareData[1]]);
    }

    /**
     * 加载语言文件
     * @param string $name
     */
    protected function loadlang($name)
    {
        Lang::load(APP_PATH . $this->request->module() . '/lang/' . $this->request->langset() . '/' . str_replace('.', '/', $name) . '.php');
    }

    /**
     * 渲染配置信息
     * @param mixed $name 键名或数组
     * @param mixed $value 值
     */
    protected function assignconfig($name, $value = '')
    {
        $this->view->config = array_merge($this->view->config ? $this->view->config : [], is_array($name) ? $name : [$name => $value]);
    }

}
