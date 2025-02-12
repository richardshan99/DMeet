<?php
namespace app\job;


use app\admin\model\User;
use app\common\library\Dict;
use app\common\library\Invite;
use app\common\library\Message;
use app\common\library\pay\Payment;
use app\common\model\InviteCall;
use app\common\model\ProjectStaffUploadedImagesFile;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use think\Db;
use think\Env;
use think\Log;
use think\queue\Job;

/**
 * 7天未成功邀约召集  自动失败
 * Class CancelInvitation
 * @package app\job
 */
class CancelInvitationCall
{
    public function fire(Job $job, $data){
        $call = InviteCall::get($data['id']);
        //退还邀请人费用
        if($call->status != Dict::INVITE_CALL_STATUS_PROCESS) { //不是进行中的邀约召集 不处理
            $job->delete();
            return;
        }

        if($job->attempts() > 3) {
            $job->delete();
            return;
        }

        $user = \app\common\model\User::get($call->user_id);
        try {
            $library = new Payment($user);
            $library->refundInvitationCall($call);
        } catch (\Exception $ex) {
            return ;
        }

        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        $job->delete();

    }

    public function failed($data){
        // ...任务达到最大重试次数后，失败了
        Log::info("7天未成功邀约召集  失败，invite ID为 ".$data['id']);
    }

}