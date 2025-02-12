<?php

namespace app\common\model;

use app\common\library\Dict;
use think\Model;

/**
 * 会员管理
 */
class UserMember Extends Model
{

    protected $name = 'user_member';
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

    public function getPayTypeTextAttr($value, $row)
    {
        return Dict::getPayType($row['pay_type']);
    }

    public function user()
    {
        return $this->belongsTo('app\common\model\User')->setEagerlyType(0);
    }

    public function member()
    {
        return $this->belongsTo('app\common\model\Member')->setEagerlyType(0);
    }

}
