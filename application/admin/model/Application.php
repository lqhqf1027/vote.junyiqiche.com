<?php

namespace app\admin\model;

use think\Model;

class Application extends Model
{
    // 表名
    protected $name = 'application';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [

    ];

    /**
     * 自动获取转义 投票名称
     * @param $value
     * @return null|string|string[]
     */
    public function getNameAttr($value){
        return emoji_decode($value);
    }

    public function wechatUser()
    {
        return $this->belongsTo('Wechatuser', 'wechat_user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    







}
