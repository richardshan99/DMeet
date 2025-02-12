<?php

namespace app\common\model;

use think\Model;

/**
 * 留言消息模版
 */
class Message Extends Model
{

    protected $name = 'message';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];
    protected $insert = [
    ];

}
