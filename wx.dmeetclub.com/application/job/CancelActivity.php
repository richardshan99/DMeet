<?php
namespace app\job;


use app\common\library\Dict;
use app\common\library\Invite;
use app\common\library\Message;
use app\common\model\ActivityUser;
use app\common\model\ProjectStaffUploadedImagesFile;
use app\common\model\User;
use app\common\model\UserRule;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use think\Db;
use think\Env;
use think\Log;
use think\queue\Job;

/**
 * 活动 - 后台取消
 * Class CancelActivity
 * @package app\job
 */
class CancelActivity
{
    public function fire(Job $job, $data){
        $user = User::get($data['user_id']);
        $payment = new \app\common\library\pay\Payment($user);
        $activityUser = ActivityUser::get($data['activity_user_id']);
        $isDone = false;
        try {
            $payment->refundActivityBackend($activityUser);
            $isDone = true;
        } catch (\Exception $ex) {
            $isDone = false;
        }

        if($job->attempts() > 3) {
            $job->delete();
            return;
        }
        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        if($isDone === true) {
            try {
                $library = new Message($user);
                $library->cancelActivity($activityUser);
            } catch (\Exception $ex) {}
            $job->delete();
        }
    }

    public function failed($data){
        // ...任务达到最大重试次数后，失败了
        Log::info("后台取消活动，invite ID为 ".$data['id']);
    }

}