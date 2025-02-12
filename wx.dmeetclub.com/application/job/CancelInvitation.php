<?php
namespace app\job;


use app\admin\model\User;
use app\common\library\Dict;
use app\common\library\Invite;
use app\common\model\ProjectStaffUploadedImagesFile;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use think\Db;
use think\Env;
use think\Log;
use think\queue\Job;

/**
 * 48小时未确认  自动拒绝邀约
 * Class CancelInvitation
 * @package app\job
 */
class CancelInvitation
{
    public function fire(Job $job, $data){
        //退还邀请人费用
        if($data['status'] != Dict::INVITE_STATUS_WAIT_CONFIRM) { //不是待确认的邀约 不处理
            $job->delete();
            return;
        }

        //被邀请人
        $user = \app\common\model\User::get($data['invite_user_id']);
        //邀约
        $invite = \app\common\model\Invite::get($data['id']);
        if(!$user || !$invite) {
            $job->delete();
            return;
        }

        try {
            $library = new Invite($user, $invite);
            $library->reject();
        } catch (\Exception $ex) {

        }

        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        $job->delete();
    }

    public function failed($data){
        // ...任务达到最大重试次数后，失败了
        Log::info("自动取消邀约失败，invite ID为 ".$data['id']);
    }

}