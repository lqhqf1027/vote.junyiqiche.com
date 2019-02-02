<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\Token;

use app\admin\model\Record;
use app\admin\model\Banner;
use app\admin\model\Application;
use app\admin\model\Wechatuser;
use app\admin\model\RideSharing;
use app\common\model\Config as ConfigModel;
use think\Db;
use think\Session;
use think\Config;
use think\Request;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';
    protected $model= '';
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('app\admin\model\RideSharing');
    }

    public function index()
    {
        $datas =collection(RideSharing::all(['status'=>'normal']))->toArray() ;
         //查询config表分享配置一起assign出去
        pr($datas);die;
        $this->assign('resData',$datas);
        return $this->view->fetch();
    }


    /**卡片分享
     * @return false|string
     */
    public function sharedata()
    {
        $url = input('urll');//获取当前页面的url，接收请求参数

        $root['url'] = $url;
        //获取access_token，并缓存
        $file = RUNTIME_PATH . '/access_token';//缓存文件名access_token
        $appid = Config::get('oauth')['appid']; // 填写自己的appid
        $secret = Config::get('oauth')['appsecret']; // 填写自己的appsecret
        $expires = 3600;//缓存时间1个小时
        if (file_exists($file)) {
            $time = filemtime($file);
            if (time() - $time > $expires) {
                $token = null;
            } else {
                $token = file_get_contents($file);
            }
        } else {
            fopen("$file", "w+");
            $token = null;
        }
        if (!$token || strlen($token) < 6) {
            $res = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret . "");

            $res = json_decode($res, true);
            $token = $res['access_token'];
// write('access_token', $token, 3600);
            @file_put_contents($file, $token);
        }

        //获取jsapi_ticket，并缓存
        $file1 = RUNTIME_PATH . '/jsapi_ticket';
        if (file_exists($file1)) {
            $time = filemtime($file1);
            if (time() - $time > $expires) {
                $jsapi_ticket = null;
            } else {
                $jsapi_ticket = file_get_contents($file1);
            }
        } else {
            fopen("$file1", "w+");
            $jsapi_ticket = null;
        }
        if (!$jsapi_ticket || strlen($jsapi_ticket) < 6) {
            $ur = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$token&type=jsapi";
            $res = file_get_contents($ur);
            $res = json_decode($res, true);
            $jsapi_ticket = $res['ticket'];
            @file_put_contents($file1, $jsapi_ticket);
        }

        $timestamp = time();//生成签名的时间戳
        $metas = range(0, 9);
        $metas = array_merge($metas, range('A', 'Z'));
        $metas = array_merge($metas, range('a', 'z'));
        $nonceStr = '';
        for ($i = 0; $i < 16; $i++) {
            $nonceStr .= $metas[rand(0, count($metas) - 1)];//生成签名的随机串
        }

        $string1 = "jsapi_ticket=" . $jsapi_ticket . "&noncestr=" . $nonceStr . "&timestamp=" . $timestamp . "&url=" . $url . "";
        $signature = sha1($string1);
        $root['appid'] = $appid;
        $root['nonceStr'] = $nonceStr;
        $root['timestamp'] = $timestamp;
        $root['signature'] = $signature;

        return json_encode($root);
    }




    /**
     *司机发布顺风车接口
     */
    public function submit_tailwind()
    {
        if($this->request->isAjax()){ 
           
            $data = $this->request->post('datas/a'); 
            $res = $this->model->allowField(true)->save($data)?$this->success('发布成功', 'success'):$this->error('发布成功', 'error');
        } 

    }

    /**
     * 顺风车列表接口
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function downwind()
    {
        $time = time();
        $type = $this->request->post('type');

        if (!$type) {
            $this->error('缺少参数，请求失败', 'error');
        }

        $field = $type == 'driver' ? ',money' : null;

        $takeCarList = RideSharing::field('id,starting_point,destination,starting_time,number_people,note,phone' . $field)
            ->order('createtime desc')->where('type', $type)->select();
        $overdueId = [];

        $takeCar = [];

        foreach ($takeCarList as $k => $v) {
            if ($time > strtotime($v['starting_time'])) {
                $overdueId[] = $v['id'];
            } else {
                $takeCar[] = $v;
            }
        }


        if ($overdueId) {
            RideSharing::where('id', 'in', $overdueId)->update(['status' => 'hidden']);
        }

        return json_encode(['takeCarList' => $takeCar]);

        $this->success('请求成功', ['takeCarList' => $takeCar]);
    }



}
