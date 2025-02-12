<?php

namespace app\common\model;

use app\common\library\Dict;
use think\Model;

/**
 * 活动类型
 */
class ActivityType Extends Model
{

    protected $name = 'activity_type';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // 追加属性
    protected $append = [
    ];

    protected $insert = [
        "is_delete" => Dict::IS_FALSE
    ];


    /**===========================
     *   Extra
    =============================*/

    /**
     * 列表
     */
    public function getList()
    {
        return $this->where('is_delete', Dict::IS_FALSE)->field('id,name')->select();
    }

}
