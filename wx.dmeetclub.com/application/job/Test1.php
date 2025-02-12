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
class Test1
{
    public function fire(Job $job, $data){
         Log::info("aaaaa，invite ID为 ".$data['id']);
    //   model('app\common\model\User')->get($data['id']);
    //   $job->delete();
    }

    public function failed($data){
        // ...任务达到最大重试次数后，失败了
        Log::info("后台取消活动，invite ID为 ".$data['id']);
    }

}