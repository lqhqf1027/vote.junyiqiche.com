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
        //活动结束时间
        $voteEndTime = ConfigModel::where('name', 'vote_end')->value('value');

        $voteEndTime = date('m月d日H时i分s秒', strtotime($voteEndTime));

        if($voteEndTime[0]==0){
            $len = intval(strlen($voteEndTime))-1;
            $voteEndTime = substr($voteEndTime,-$len);
        };
        //参选信息
//        $contestant = collection(Application::field('id,name,applicationimages,votes')
//        ->with(['wechatUser'=>function ($q){
//            $q->withField('id,sex');
//        }])->where('status','normal')->select())->toArray();
        $contestant = $this->test(['status'=>'normal']);

        foreach ($contestant as $k=>$v){
            $contestant[$k]['applicationimages'] = $v['applicationimages']?explode(';',$v['applicationimages'])[0]:'';
            $contestant[$k]['is_vote'] = 0;
        }

        if(Session::get('MEMBER')){
            $user_id = Session::get('MEMBER')['id'];
        }

        if(!empty($user_id)){
            //已经投票的ID
           $voted_id = Record::where('wechat_user_id',$user_id)->whereTime('create_time', 'today')->column('application_id');

           if($voted_id){
               foreach ($contestant as $k=>$v){
                   $contestant[$k]['is_vote'] = in_array($v['id'],$voted_id)?1:0;
               }
           }

        }

        $data = [
            'voteEndTime'=>$voteEndTime,
            'contestantList'=>$contestant
        ];
        pr(array_merge($this->statisticsBanner(),$data));
        $this->view->assign(array_merge($this->statisticsBanner(),$data));

        return $this->view->fetch();
    }

    public function news()
    {
        $newslist = [];
        return jsonp(['newslist' => $newslist, 'new' => count($newslist), 'url' => 'https://www.fastadmin.net?ref=news']);
    }

    public function vote()
    {
        $user_id = $this->request->post('user_id');

        $application_id = $this->request->post('application_id');

        Record::create([
            'wechat_user_id'=>$user_id,
            'application_id'=>$application_id
        ]);
    }

    /**
     * 统计和轮播
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function statisticsBanner()
    {
        $bannerList = collection(Banner::where('status','normal')->select())->toArray();
        //参赛者数量
        $applicationCount = Application::where('status', 'normal')->count('id');
        //投票数
        $voteCount = Application::where('status', 'normal')->sum('votes');
        //访问量
        $visitCount = Wechatuser::Count('id');

        return [
            'bannerList'=>$bannerList,
            'statistics'=>[
                'applicationCount'=>$applicationCount,
                'voteCount'=>$voteCount,
                'visitCount'=>$visitCount
            ],
        ];
    }

    public function searchPlayer()
    {
//        if($this->request->isAjax()){
            $search = input('search');

            if(is_numeric($search)){
               $result = collection(Application::all($search))->toArray();
            }else{
               $result = collection(Application::all(['name'=>$search]))->toArray();
            }
            return json_encode($result);
//        }
    }

    public function test($where)
    {
        $contestant = collection(Application::field('id,name,applicationimages,votes')
            ->with(['wechatUser'=>function ($q){
                $q->withField('id,sex');
            }])->where($where)->select())->toArray();

        return $contestant;
    }

}
