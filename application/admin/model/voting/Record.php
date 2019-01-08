<?php

namespace app\admin\model\voting;

use think\Model;

class Record extends Model
{
    // 表名
    protected $name = 'voting_record';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'votetime_text'
    ];
    

    



    public function getVotetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['votetime']) ? $data['votetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setVotetimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
