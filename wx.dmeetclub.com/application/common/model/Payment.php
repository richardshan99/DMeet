<?php

namespace app\common\model;

use app\common\library\Dict;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 * 财务
 */
class Payment Extends Model
{

    protected $name = 'payment';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // 追加属性
    protected $append = [
    ];

    protected $insert = [
        "is_use" => Dict::IS_FALSE,
        "is_refund" => Dict::IS_FALSE
    ];

    protected $type = [
    ];

    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id')->setEagerlyType(0);
    }


}
