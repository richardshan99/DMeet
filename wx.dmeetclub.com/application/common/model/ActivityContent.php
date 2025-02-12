<?php

namespace app\common\model;

use app\common\library\Dict;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 * 活动扩展数据
 */
class ActivityContent Extends Model
{

    protected $name = 'activity_content';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [

    ];

}
