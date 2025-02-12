<?php

namespace app\common\model;

use app\common\library\Dict;
use think\Model;

/**
 * 对话关联
 */
class ChatRelation Extends Model
{

    protected $name = 'chat_relation';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // 追加属性
    protected $append = [
    ];
    protected $insert = [
        "unread_num" => 0
    ];

    /**
     * @param $value
     * @param $row
     * @return false|string
     */
    public function getCreateTimeTextAttr($value, $row)
    {
        return date("Y-m-d H:i", $row['create_time']);
    }

    /**
     * @param $value
     * @param $row
     * @return false|string
     */
    public function getUpdateTimeTextAttr($value, $row)
    {
        return date("Y-m-d H:i", $row['update_time']);
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function relationuser()
    {
        return $this->belongsTo('app\common\model\User', 'relation_user_id')->setEagerlyType(0);
    }

}
