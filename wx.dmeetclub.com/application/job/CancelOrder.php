<?php
namespace app\job;


use app\common\library\Dict;
use app\common\library\Invite;
use app\common\model\User;
use app\common\model\UserRule;
use think\Db;
use think\Env;
use think\Log;
use think\queue\Job;

/**
 * 30min未核销。 取消邀约
 * Class CancelOrder
 * @package app\job
 */
class CancelOrder
{
    public function fire(Job $job, $data){
        try {
            Log::init(['type'  =>  'File', 'path'  =>  ROOT_PATH.'logs/cancel-order-job/']);
            Log::write( '进入了'.'ID = '.$data['id']);
            //不是待见面的邀约 不处理
            if($data['status'] != Dict::INVITE_STATUS_WAIT_MEET) {
                $job->delete();
                return;
            }
    
            //邀约
            $invite = \app\common\model\Invite::get($data['id']);
            //都没核销
            if($invite->inviter_is_verify == Dict::IS_FALSE && $invite->invitee_is_verify == Dict::IS_FALSE) {
                $job->delete();
                return;
            }
            //退款方信息
            $userId = $invite->inviter_is_verify == Dict::IS_TRUE ? $invite->user_id : $invite->invite_user_id;
            $user = User::get($userId);
            $library = new Invite($user, $invite);
            $library->cancelOrder();
        }catch (\Throwable $e){
            Log::init(['type'  =>  'File', 'path'  =>  ROOT_PATH.'logs/cancel-order-job/']);
            Log::write( $e->getMessage().'ID = '.$data['id']);
        }
        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        $job->delete();
    }

    public function failed($data){
        // ...任务达到最大重试次数后，失败了
        Log::info("超时未核销自动取消邀约失败，invite ID为 ".$data['id']);
    }

}