<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/1/8
 * Time: 18:02
 */

namespace app\admin\model;


use think\Model;

class Banner extends Model
{
// 表名
    protected $name = 'banner';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    // 追加属性
    protected $append = [

    ];

    public function getStautsList()
    {
        return ['normal'=>'正常','hidden'=>'隐藏'];
    }
}