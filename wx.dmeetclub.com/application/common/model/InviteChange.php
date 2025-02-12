<?php

namespace app\common\model;

use app\common\library\Dict;
use think\Db;
use think\Model;

/**
 * 邀约见面信息修改表
 */
class InviteChange Extends Model
{

    protected $name = 'invite_change';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    /**
     * 状态 格式化
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getStatusTextAttr($value, $row)
    {
        return Dict::getInviteChangeStatus($row['status']);
    }

    /**
     * 见面时间 格式化
     * @param $value
     * @param $row
     * @return false|string
     */
    public function getMeetTimeTextAttr($value, $row)
    {
        return $row['meet_time'] ? date('Y-m-d H:i', $row['meet_time']) : "";
    }

    /**
     * 处理见面信息修改
     * @param $inviteId
     * @param $scene  // agree 同意，refuse 拒绝
     * @param $user
     * @return array
     */
    public function handleChange($inviteId,$scene,$user): array
    {
        Db::startTrans();
        try{
            $invite = model('app\common\model\Invite')
                ->where([
                    "id" => $inviteId,
                    "user_id" => $user->id,
                    "status" => Dict::INVITE_STATUS_WAIT_CONFIRM,
                ])->find();
            if(!$invite) {
                exception("未找到邀约信息");
            }

            $inviteChangeInfo = (new InviteChange)->where(['invite_id'=>$inviteId])->find();
            if(empty($inviteChangeInfo)){
                exception("您已修改过见面信息");
            }
            if($inviteChangeInfo['status'] != Dict::INVITE_CHANGE_STATUS_1){
                exception("该见面信息已处理");
            }
            if($scene == 'agree'){
                $status = Dict::INVITE_CHANGE_STATUS_2;
                $inviteRes = $invite->save([
                    'shop_id' => $inviteChangeInfo['shop_id'],
                    'address' => $inviteChangeInfo['address'],
                    'meet_time' => $inviteChangeInfo['meet_time'],
                ]);
                if(!$inviteRes){
                    exception("修改邀约信息失败");
                }
            }else{
                $status = Dict::INVITE_CHANGE_STATUS_3;
            }
            $inviteChangeRes = $inviteChangeInfo->save(['status'=>$status]);
            if(!$inviteChangeRes){
                exception("修改见面信息状态失败");
            }
            Db::commit();
        }catch (\Throwable $e){
            Db::rollback();
            return [false,$e->getMessage()];
        }
        return [true,''];
    }



}
