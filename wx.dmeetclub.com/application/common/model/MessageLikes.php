<?php

namespace app\common\model;

use app\common\library\Dict;
use app\common\library\pay\Payment;
use think\Model;

/**
 * 消息 - 点赞
 */
class MessageLikes Extends Model
{

    protected $name = 'message_likes';
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

    protected $type = [

    ];

    public function getImageTextAttr($value, $item)
    {
        return $item['image'] ? cdnurl($item['image'], true) : "";
    }

    public function getCreateTimeTextAttr($value, $item)
    {
        return $item['create_time'] ? date("Y-m-d H:i", $item['create_time']) : "";
    }

    public function likeuser()
    {
        return $this->belongsTo('app\common\model\User', 'like_user_id')->setEagerlyType(0);
    }
}
