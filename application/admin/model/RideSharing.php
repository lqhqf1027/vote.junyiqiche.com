<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/1/29
 * Time: 11:37
 */

namespace app\admin\model;


use think\Model;

class RideSharing extends Model
{
    protected $name = 'ride_sharing';

    protected $autoWriteTimestamp = true;

    protected $createTime = 'createtime';

    protected $updateTime = 'updatetime';

   
    // 定义全局的查询范围
    protected function base($query)
    {
        $query->where('status','normal');
    }


}