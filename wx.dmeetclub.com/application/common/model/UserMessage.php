<?php

namespace app\common\model;

use app\common\library\Dict;
use app\common\library\pay\Payment;
use DI\CompiledContainer;
use think\Model;

/**
 * 用户消息
 */
class UserMessage Extends Model
{

    protected $name = 'user_message';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [

    ];

    protected $type = [

    ];

    /**
     * 计算所有新消息数量
     * @param User $user
     * @return string
     * @throws \think\Exception
     */
    public function calNewMessageCount(User $user)
    {
        if(!$user instanceof  User) return 0;

        return bcadd(
            bcadd($this->calChat($user), $this->calInvitation($user), 0),
            bcadd($this->calLikes($user), $this->calSystem($user), 0)
        );
    }

    /**
     * 计算新消息数量 -- 打招呼
     * @param User $user
     * @return int
     * @throws \think\Exception
     */
    public function calChat(User $user)
    {
        return model('app\common\model\ChatRelation')->where([
            "user_id" => $user->id,
        ])->sum('unread_num');
    }

    /**
     * 计算新消息数量 -- 邀约
     * @param User $user
     * @return int
     * @throws \think\Exception
     */
    public function calInvitation(User $user)
    {

        $lastInvitationTime = $this->where('user_id', $user->id)->value('last_invitation_time');

        return model('app\common\model\MessageInvitation')->where([
            'user_id' => $user->id,
            'create_time' => ['>=', $lastInvitationTime ?: 0]
        ])->count();
    }

    /**
     * 计算新消息数量 -- 点赞
     * @param User $user
     * @return int
     * @throws \think\Exception
     */
    public function calLikes(User $user)
    {
        if(!$user instanceof  User) return 0;
        $lastLikeTime = $this->where('user_id', $user->id)->value('last_likes_time');

        return model('app\common\model\MessageLikes')->where([
            'user_id' => $user->id,
            'create_time' => ['>=', $lastLikeTime ?: 0]
        ])->count();
    }

    /**
     * 计算新消息数量 -- 系统消息
     * @param User $user
     * @return int
     */
    public function calSystem(User $user)
    {
        return model('app\common\model\MessageSystem')
            ->where([
                "user_id" => $user->id,
                "is_read" => Dict::IS_FALSE
            ])->count();
    }

    /**
     * 刷新邀约时间
     * @param User $user
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function flushInvitationTime(User $user)
    {
        $this->flushLastTime($user, ['last_invitation_time' => time()]);
    }

    /**
     * 刷新点赞时间
     * @param User $user
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function flushLikesTime(User $user)
    {
        $this->flushLastTime($user, ['last_likes_time' => time()]);
    }

    /**
     * 刷新系统消息时间
     * @param User $user
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function flushSystemTime(User $user)
    {
        $this->flushLastTime($user, ['last_system_time' => time()]);
    }

    /**
     * 刷新招呼时间
     * @param User $user
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function flushSalutationTime(User $user)
    {
        $this->flushLastTime($user, ['last_salutation_time' => time()]);
    }

    /**
     * 更新最后查看时间
     * @param User $user
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function flushLastTime(User $user, $data)
    {
        $r = $this->where('user_id', $user->id)->find();
        if(!$r) {
            $r = self::create(['user_id' => $user->id]);
        }
        $r->allowField(true)->save($data);
    }
}
