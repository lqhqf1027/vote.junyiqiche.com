<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/2/1
 * Time: 11:01
 */

namespace app\index\controller;


use app\common\controller\Api;
use app\admin\model\RideSharing;

class Freeride extends Api
{

    protected $noNeedLogin = ['*'];

    /**
     *司机发布顺风车接口
     */
    public function submit_tailwind()
    {
        $arr = [
            'phone' => '18683787363',
            'starting_time' => '2019-02-19 10:56:09',
            'starting_point' => '火车北站',
            'destination' => '万年场',
            'money' => '70',
            'number_people' => 2,
            'note' => '马上开了',
            'type'=>'driver'
        ];

        $user_id = $this->request->post('user_id');

        $info = $this->request->post('info/a');


        if (!$user_id || !$info) {
            $this->error('缺少参数，请求失败', 'error');
        }

        $info['user_id'] = $user_id;

        RideSharing::create($info) ? $this->success('发布成功', 'success') : $this->error('发布失败', 'error');

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

//        return json_encode(['takeCarList' => $takeCar]);

        $this->success('请求成功', ['takeCarList' => $takeCar]);
    }
}