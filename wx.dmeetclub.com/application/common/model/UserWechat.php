<?php

namespace app\common\model;

use think\Model;

/**
 * 公众号用户
 */
class UserWechat Extends Model
{

    protected $name = 'user_wechat';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

}
