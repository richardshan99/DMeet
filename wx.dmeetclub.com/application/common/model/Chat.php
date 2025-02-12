<?php

namespace app\common\model;

use app\common\library\Dict;
use think\Db;
use think\Model;

/**
 * 对话
 */
class Chat Extends Model
{

    protected $name = 'chat';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];
    protected $insert = [
        "is_read" => Dict::IS_FALSE
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
     * @return \think\model\relation\BelongsTo
     */
    public function fromuser()
    {
        return $this->belongsTo('app\common\model\User', 'from_user_id')->setEagerlyType(0);
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function touser()
    {
        return $this->belongsTo('app\common\model\User', 'to_user_id')->setEagerlyType(0);
    }

    /**
     * 发送消息
     * @param $fromUserId
     * @param $toUserId
     * @param $message
     * @return Chat
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function send($fromUserId, $toUserId, $message)
    {
        Db::startTrans();
        try {
            //创建对话关联
            $relation = model('app\common\model\ChatRelation')->where([
                "user_id" => $toUserId, //被撩的人
                "relation_user_id" => $fromUserId // 撩别人的人
            ])->find();
            if(!$relation) {
                $relation = ChatRelation::create([
                    "user_id" => $toUserId, //被撩的人
                    "relation_user_id" => $fromUserId // 撩别人的人
                ], true);
            }
            //需要用到自动更新时间
            $relation->unread_num = $relation->unread_num+1;
            $relation->save();

            $result = self::create([
                'from_user_id' => $fromUserId,
                'to_user_id'   => $toUserId,
                'message'      => $message['content'],
                'longitude_and_latitude' => $message['longitude_and_latitude'] ?? ''
            ]);
            Db::commit();
        } catch (\Exception $ex) {
            Db::rollback();
            return false;
        }
        return $result;
    }

    /**
     * 获取对话列表
     * @param $userId 当前登录用户
     * @param $chatUserId 对面用户
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($userId, $chatUserId)
    {
        //更新未读数量
        model('app\common\model\ChatRelation')->save([
            "unread_num" => 0
        ], ['user_id' => $userId, 'relation_user_id' => $chatUserId]);
        model('app\common\model\Chat')->save([
            "is_read" => Dict::IS_TRUE
        ], [
            "from_user_id" => $chatUserId,
            "to_user_id"   => $userId
        ]);

        $list = $this->with(['fromuser', 'touser'])
            ->where(function ($query) use($userId, $chatUserId) {
                $query->whereRaw(
                    "from_user_id = {$userId} and to_user_id = {$chatUserId} or 
                     from_user_id = {$chatUserId} and to_user_id = {$userId}"
                );
            })
            ->order('create_time', 'desc')
            ->paginate(20);
        foreach ($list as $key => $item)
        {
            $item->role_type = "";
            $item->avatar = cdnurl($item->fromuser->avatar, true);
            if($item->from_user_id == $userId) {
                $item->role_type = 1;
            }
            if($item->to_user_id == $userId) {
                $item->role_type = 2;
            }
            $item->visible(['role_type', 'message','longitude_and_latitude', 'avatar']);
            $item->append(["create_time_text"]);
        }

        //倒序查出的数据需要反转
        $items = $list->toArray();
        $data = array_reverse($items['data']);
        $items['data'] = $data;
        return $items;
    }
}
