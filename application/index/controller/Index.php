<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\Token;

use app\admin\model\Record;
use app\admin\model\Banner;
use app\admin\model\Application;
use app\admin\model\Wechatuser;
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

    public function _initialize()
    {
        parent::_initialize();

    }

    public function index()
    {

//        $contestant = $this->playerInfo(['status' => 'normal'], 'id,name,applicationimages,votes');

        $contestant = Application::field('id,name,applicationimages,votes,describe_yourself')
            ->with(['wechatUser' => function ($q) {
                $q->withField('id,sex');
            }])->where(['status' => 'normal'])->order('id desc')->paginate(20);


        $pages = $contestant->render();

        $contestant = $contestant->toArray();

        foreach ($contestant['data'] as $k => $v) {
            $contestant['data'][$k]['applicationimages'] = $v['applicationimages'] ? explode(';', $v['applicationimages'])[0] : '';
            $contestant['data'][$k]['is_vote'] = 0;

        }
        if ($contestant['data']) {


            $arr = array_values(arraySort($contestant['data'], 'votes', 1));

            $arr = [array_merge($arr[0], ['prize' => 'https://static.yc.junyiqiche.com/uploads/top1.png']), array_merge($arr[1], ['prize' => 'https://static.yc.junyiqiche.com/uploads/top2.png']), array_merge($arr[2], ['prize' => 'https://static.yc.junyiqiche.com/uploads/top3.png'])];

            foreach ($contestant['data'] as $k => $v) {
                foreach ($arr as $key => $value) {
                    if ($v['id'] == $value['id']) {
                        unset($contestant['data'][$k]);
                    }
                }
            }
            $as = $contestant['data'];
            $contestant['data'] = [];
            $contestant['data']['prize'] = $arr;
            $contestant['data']['normal'] = $as;
            if (!$this->user_id) {
                //已经投票的ID
                $voted_id = Record::where('wechat_user_id', $this->user_id)->whereTime('votetime', 'today')->column('application_id');

                if ($voted_id) {
                    foreach ($contestant['data']['normal'] as $k => $v) {
                        $contestant['data']['normal'][$k]['is_vote'] = in_array($v['id'], $voted_id) ? 1 : 0;
                    }
                }


            }

        }
        $data = array_merge($this->publicData(), ['contestantList' => $contestant]);

        //pr($contestant);
            $this->view->assign(['data' => $data,
                'page' => $pages
            ]);

//        pr($data);die;
        return $this->view->fetch();
    }

    public function lazyPlayerInfo()
    {
        if ($this->request->isGet()) {
            $page = input('page');
            $page = $page . ',10';

            return json_encode($this->playerInfo(['status' => 'normal'], 'id,name,applicationimages,votes', null, $page));
        }
    }


    /**
     * 上传封面
     * @return string
     */
    public function uploadsHeaderImg()
    {
        return action('api/common/upload');
    }

    /**
     * 提交报名
     */
    public function sendVote()
    {
        if ($this->request->isPost()) {
            $res = new Application();
            $data_new = $this->request->post()['datas'];
            $data_new['name'] = emoji_encode($data_new['name']);
            $data_new['describe_yourself'] = emoji_encode($data_new['describe_yourself']);

            $data = $res->allowField(true)->save($data_new);
            //判断是否重复报名
            $check = Application::get(['name'=>$data_new['name'],'applicationimages'=>$data_new['applicationimages']]);

            if(!$check){
                $data =  $res->allowField(true)->save($data_new);
            }else{
                $this->error('不能重复报名');
            }
            if ($data) {
                $this->success('报名成功！');
            } else {
                $this->error('报名失败');
            }

        } else {
            $this->error('非法请求');
        }
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
     * 投票
     * @return string
     * @throws \think\Exception
     */
    public function vote()
    {

        if ($this->request->isAjax()) {
            // pr($_POST);
            // die;
            $user_id = $_POST['user_id'];

            $application_id = $_POST['application_id'];

            // pr($application_id);
            // pr($user_id);
            // die;
            $result = Record::create([
                'wechat_user_id' => $user_id,
                'application_id' => $application_id
            ]);

            if ($result) {
                Application::where('id', $application_id)->setInc('votes');
            }

            return $result ? '投票成功' : '投票失败';
        }

    }

    /**
     * 得票排序
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function ranking()
    {

        $data = $this->publicData();

        $this->view->assign('data', $data);
        return $this->view->fetch();
    }

    public function rankingMore()
    {
        if ($this->request->isAjax()) {
            $page = $this->request->post('page');

            $page = $page . ',25';

            $ranking = $this->getRanking($page);

            return json_encode($ranking);
        }
    }

    public function getRanking($page)
    {
        return collection(Application::where('status', 'normal')
            ->field('id,name,votes')
            ->order('votes desc')
            ->page($page)
            ->select())->toArray();
    }


    /**
     * 活动规则
     * @return string
     * @throws \think\Exception
     */
    public function rules()
    {
        //得到活动规则
        $vote_rules = ConfigModel::get(['name' => 'vote_rules'])->value;

        $data = array_merge($this->publicData(), ['vote_rules' => $vote_rules]);

        $this->view->assign('data', $data);
        return $this->view->fetch();
    }

    /**
     * 公共数据
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function publicData()
    {
        $bannerList = collection(Banner::where('status', 'normal')->select())->toArray();
        //参赛者数量
        $applicationCount = Application::where('status', 'normal')->count('id');
        //投票数
        $voteCount = Application::where('status', 'normal')->sum('votes');
        //访问量
        $visitCount = Wechatuser::Count('id');

        $isOverdue = 0;           //活动进行中

        //活动结束时间
        $voteEndTime = ConfigModel::where('name', 'vote_end')->value('value');
        //活动开始时间
        $voteStartTime = strtotime(ConfigModel::get(['name' => 'vote'])->value);
        $time = time();
        if ($time < $voteStartTime) {
            $isOverdue = 1;        //未开始
        }

        if ($time > strtotime($voteEndTime)) {
            $isOverdue = 2;        //已结束
        }

        $voteEndTime = date('m月d日H时i分s秒', strtotime($voteEndTime));


        if ($voteEndTime[0] == 0) {
            $voteEndTime = substr($voteEndTime, 1);
        };

        //判断今日是否已经投票
        $isTodayVote = 0;
        //判断该用户是否报过名
        $is_application = 0;


        if ($this->user_id == true) {

            //已经投票的ID
            $voted_id = Record::where('wechat_user_id', $this->user_id)->whereTime('votetime', 'today')->column('application_id');

            if ($voted_id) {
                $isTodayVote = 1;
                Session::set('isTodayVote', $isTodayVote);
            }

            //判断该用户是否报名
            $checkApplication = Application::where([
                'status' => 'normal',
                'wechat_user_id' => $this->user_id
            ])->find();

            if ($checkApplication) {
                $is_application = 1;
            }
        }

        $backMusicUrl = ConfigModel::get(['name' => 'link'])->value;
        $backMusicSwitch = ConfigModel::get(['name' => 'switch'])->value;

        return [
            'bannerList' => $bannerList,
            'voteEndTime' => $voteEndTime,
            'statistics' => [
                'applicationCount' => $applicationCount,
                'voteCount' => $voteCount,
                'visitCount' => $visitCount
            ],
            'backGroundMusic' => [
                'url' => $backMusicUrl,
                'switch' => $backMusicSwitch
            ],
            'is_application' => $is_application,
            'isTodayVote' => $isTodayVote,
            'isOverdue' => $isOverdue
        ];
    }

    /**
     * 搜索选手
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function searchPlayer($search)
    {

        if (is_numeric($search)) {
            $result = $this->playerInfo(['status' => 'normal'], 'id,name,applicationimages,votes', $search);
        } else {
            $result = $this->playerInfo(['status' => 'normal', 'name' => $search], 'id,name,applicationimages,votes');
        }

        $data = array_merge($this->publicData(), ['contestantList' => ['data' => $result]]);

        $this->view->assign('data', $data);

        return $this->view->fetch();
    }

    /**
     * 选手信息
     * @param $where
     * @param null $select
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function playerInfo($where, $field, $select = null, $page = null)
    {
        $contestant = collection(Application::field($field)
            ->with(['wechatUser' => function ($q) {
                $q->withField('id,sex');
            }])->where($where)->order('id desc')->page($page)->select($select))->toArray();

        foreach ($contestant as $k => $v) {
            $contestant[$k]['applicationimages'] = $v['applicationimages'] ? explode(';', $v['applicationimages'])[0] : '';
            $contestant[$k]['is_vote'] = 0;
        }


        if ($this->user_id == true) {
            //已经投票的ID
            $voted_id = Record::where('wechat_user_id', $this->user_id)->whereTime('votetime', 'today')->column('application_id');

            if ($voted_id) {
                foreach ($contestant as $k => $v) {
                    $contestant[$k]['is_vote'] = in_array($v['id'], $voted_id) ? 1 : 0;
                }
            }

        }

        return $contestant;

    }

    /**
     * 选手详情
     * @param $application_id
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function playerDetails($application_id)
    {
        $info = $this->playerInfo(['status' => 'normal'], 'id,name,applicationimages,votes,model,daily_running_water,service_points,describe_yourself', $application_id);

        $data = array_merge($this->publicData(), ['playerDetail' => $info[0]]);
        $data['describe_yourself'] = emoji_decode($data['describe_yourself']);
        $this->view->assign('data', $data);

        return $this->view->fetch();
    }


    /**
     * 选手详情
     * @param $application_id
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function details()
    {
        $user_id = $_POST['user_id'];

        $data = Application::where('wechat_user_id', $user_id)->find();

        return json_encode($data);

    }

    public function music()
    {
        Session::set('musics', input('ons'));
    }

    public function clearSong()
    {
        Session::delete('musics');
    }


}
