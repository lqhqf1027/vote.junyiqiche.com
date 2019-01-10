<?php

namespace app\wechat\controller;

use think\request;
use think\Controller;
use think\Loader;
use think\Config;
use think\Db;
use app\admin\model\Wechatuser as wechatUserModel;

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

    public function adduser()
    {

//        $this->model = ;
        $appid = Config::get('APPID');
        $secret = Config::get('APPSECRET');
        $code = $this->request->get('code');
        ##获取网页授权的access_token 和openid
        $rslt = gets("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code");

        ##通过access_token 和openid获取获取信息
        $user = gets("https://api.weixin.qq.com/sns/userinfo?access_token=" . $rslt['access_token'] . "&openid=" . $rslt['openid'] . "&lang=zh_CN ");
        $user['nickname'] = htmlspecialchars($user['nickname']);
        $res = wechatUserModel::get(['openid'=>$user['openid']]);
        $model = new wechatUserModel;
        if (empty($res)) {
            $insert_user = $model->allowField(true)->save($user);
            if ($insert_user) {
                session('MEMBER', collection($user)->toArray());

                $this->redirect('Index/index/index');
            } else {
                $this->error('添加失败');
            }
        } else {
            session('MEMBER', collection($res)->toArray());
            $user['id'] = $res->id;
            $update_user = $res->allowField(true)->save($user);
            $update_user == 0 ? $this->redirect('Index/index/index') : $this->redirect('Index/index/index');
        }


    }

}
