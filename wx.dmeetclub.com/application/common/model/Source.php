<?php

namespace app\common\model;

use app\common\library\Dict;
use think\Model;

/**
 * 渠道
 */
class Source Extends Model
{

    protected $name = 'source';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [
        "num" => 0,
        "num1" => 0,
        "is_delete" => Dict::IS_FALSE
    ];

    public function getCreateTimeTextAttr($value, $row)
    {
        return date("Y-m-d H:i", $row['create_time']);
    }

    /**
     * 渠道计数
     * @time 2024-9-27
     * @param User $user
     * @param int $type 1:授权登录 2:实名认证
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function statis(User $user, $type = null)
    {
        $sourceId = $user->source_id;
        //非渠道用户
        if(!$sourceId) return ;
        $source = $this->where([
            "id" => $sourceId,
            "is_delete" => Dict::IS_FALSE
        ])->find();
        //渠道不存在
        if(!$source) return;

        $source->setInc($type == 1 ? 'num1' : 'num');
    }
}
