<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\Token;

use app\admin\model\Record;
use app\admin\model\Banner;
use app\admin\model\Application;
use app\admin\model\wechat\User;
use app\common\model\Config as ConfigModel;

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
        $bannerList = collection(Banner::where('status','normal')->select())->toArray();
        //参赛者数量
        $applicationCount = Application::where('status', 'normal')->count('id');
        //投票数
        $voteCount = Application::where('status', 'normal')->sum('votes');
        //访问量
        $visitCount = User::Count('id');

        $voteEndTime = strtotime(ConfigModel::where('name', 'vote_end')->value('value'));

        $contestant = collection(Application::all(function ($q){
            $q->where('status','normal')->field('id,name,applicationimages,votes');
        }))->toArray();

        foreach ($contestant as $k=>$v){
            $contestant[$k]['applicationimages'] = $v['applicationimages']?explode(';',$v['applicationimages'])[0]:'';
        }

pr($contestant);
//        $voteEndTime = date('m月d日 H时i分s秒', strtotime($voteEndTime));
        //         $this->view->assign([
//             'applicationCount'=>$applicationCount
//         ]);

        return $this->view->fetch();
    }

    public function news()
    {
        $newslist = [];
        return jsonp(['newslist' => $newslist, 'new' => count($newslist), 'url' => 'https://www.fastadmin.net?ref=news']);
    }


}
