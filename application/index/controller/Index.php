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
        $contestant = $this->playerInfo(['status' => 'normal'], 'id,name,applicationimages,votes');

        $data = array_merge($this->publicData(), ['contestantList' => $contestant]);
//        pr($data);
        $this->view->assign($data);
        $this->view->assign('url', $_SERVER['REQUEST_URI']);
        return $this->view->fetch();
    }

    public function news()
    {
        $newslist = [];
        return jsonp(['newslist' => $newslist, 'new' => count($newslist), 'url' => 'https://www.fastadmin.net?ref=news']);
    }

    /**
     * 投票
     * @return string
     * @throws \think\Exception
     */
    public function vote()
    {
        if ($this->request->isAjax()) {
            $user_id = $this->request->post('user_id');

            $application_id = $this->request->post('application_id');

            $result = Record::create([
                'wechat_user_id' => $user_id,
                'application_id' => $application_id
            ]);

            if ($result) {
                Application::where('id', $application_id)->setInc('votes');
            }

            return $result ? 'success' : 'error';
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
        $ranking = collection(Application::where('status', 'normal')
            ->field('id,name,votes')
            ->order('votes desc')
            ->select())->toArray();

        $data = array_merge($this->publicData(), ['rankList' => $ranking]);
        $this->view->assign('url', $_SERVER['REQUEST_URI']);
        $this->view->assign($data);
        return $this->view->fetch();
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

        $this->view->assign($data);

        $this->view->assign('url', $_SERVER['REQUEST_URI']);

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

        $this->view->assign($data);

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

        $this->view->assign($data);

        return $this->view->fetch();
    }


}
