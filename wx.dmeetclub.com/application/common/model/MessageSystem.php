<?php

namespace app\common\model;

use app\common\library\Dict;
use app\common\library\pay\Payment;
use think\Model;

/**
 * 消息 - 系统消息
 */
class MessageSystem Extends Model
{

    protected $name = 'message_system';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    protected $insert = [
        "is_read" => Dict::IS_FALSE
    ];

    public function getCreateTimeTextAttr($value, $item)
    {
        return $item['create_time'] ? date("Y-m-d H:i", $item['create_time']) : "";
    }

    /**
     * 获取列表
     * @param User $user
     * @return MessageSystem
     * @throws \think\exception\DbException
     */
    public function getList(User $user)
    {
        $list = $this->where('user_id', $user->id)->order('create_time', 'desc')->paginate(20);
        foreach($list as $key => $item)
        {
            $item->visible(['message']);
            $item->append(['create_time_text']);
        }

        //变更为已读
        model('app\common\model\MessageSystem')->allowField(true)->save([
            "is_read" => Dict::IS_TRUE
        ], [
            "user_id" => $user->id,
            "is_read" => Dict::IS_FALSE
        ]);
        return $list;
    }

}
