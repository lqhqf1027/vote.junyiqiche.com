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
class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
        Session::set('user_id', (Session::get('MEMBER')['id']));
    }

    public function index()
    {

        $contestant = $this->playerInfo(['status' => 'normal'], 'id,name,applicationimages,votes');

        $data = array_merge($this->publicData(), ['contestantList' => $contestant]);
//        pr($data);
        $this->view->assign('data', $data);
        // Session::set('user_id', (Session::get('MEMBER')['id']));
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
        $appid = Config::get('APPID'); // 填写自己的appid
        $secret = Config::get('APPSECRET'); // 填写自己的appsecret
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
            $user_id = 5;

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
        $ranking = $this->getRanking('1,30');

        $data = array_merge($this->publicData(), ['rankList' => $ranking]);

        $this->view->assign('data', $data);
        return $this->view->fetch();
    }

    public function rankingMore()
    {
        if($this->request->isAjax()){
            $page = $this->request->post('page');

            $page = $page.',30';

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

        if (!empty(Session::get('MEMBER')['id'])) {
            //已经投票的ID
            $voted_id = Record::where('wechat_user_id', Session::get('MEMBER')['id'])->whereTime('votetime', 'today')->column('application_id');

            if ($voted_id) {
                $isTodayVote = 1;
                Session::set('isTodayVote', $isTodayVote);
            }

            //判断该用户是否报名
            $checkApplication = Application::where([
                'status' => 'normal',
                'wechat_user_id' => Session::get('MEMBER')['id']
            ])->find();

            if ($checkApplication) {
                $is_application = 1;
            }
        }
        
        return [
            'bannerList' => $bannerList,
            'voteEndTime' => $voteEndTime,
            'statistics' => [
                'applicationCount' => $applicationCount,
                'voteCount' => $voteCount,
                'visitCount' => $visitCount
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

        $data = array_merge($this->publicData(), ['contestantList' => $result]);

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
    public function playerInfo($where, $field, $select = null)
    {
        $contestant = collection(Application::field($field)
            ->with(['wechatUser' => function ($q) {
                $q->withField('id,sex');
            }])->where($where)->select($select))->toArray();

        foreach ($contestant as $k => $v) {
            $contestant[$k]['applicationimages'] = $v['applicationimages'] ? explode(';', $v['applicationimages'])[0] : '';
            $contestant[$k]['is_vote'] = 0;
        }


        if (!empty(Session::get('MEMBER')['id'])) {
            //已经投票的ID
            $voted_id = Record::where('wechat_user_id', Session::get('MEMBER')['id'])->whereTime('votetime', 'today')->column('application_id');

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
        $info = $this->playerInfo(['status' => 'normal'], 'id,name,applicationimages,votes,model,daily_running_water,service_points', $application_id);

        $data = array_merge($this->publicData(), ['playerDetail' => $info[0]]);

        $this->view->assign('data', $data);

        return $this->view->fetch();
    }


}
