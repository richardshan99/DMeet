<?php

namespace app\common\model;

use think\Model;

/**
 * 用户新地点需求反馈
 */
class UserAddressFeedback Extends Model
{

    protected $name = 'user_address_feedback';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];


    public function user()
    {
        return $this->belongsTo('app\common\model\User')->setEagerlyType(0);
    }
}
