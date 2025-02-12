<?php

namespace app\common\model;

use app\common\library\Dict;
use fast\Tree;
use think\Model;

/**
 * 用户关注
 */
class UserFollow Extends Model
{

    protected $name = 'user_follow';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;

    /**
     * 当前用户和待关注用户是否有关注关系
     * @param $userId
     * @param $followUserId
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isExist($userId, $followUserId)
    {
        $follow = $this->where([
            "user_id"       => $userId,
            "follow_user_id" => $followUserId,
        ])->find();

        return $follow ? Dict::IS_TRUE : Dict::IS_FALSE;
    }

    /**
     * 关注者
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id')->setEagerlyType(0);
    }

    /**
     * 被关注者
     * @return \think\model\relation\BelongsTo
     */
    public function followuser()
    {
        return $this->belongsTo('app\common\model\User', 'follow_user_id')->setEagerlyType(0);
    }

}
