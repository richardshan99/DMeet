<?php

namespace app\common\command;


use app\common\library\Dict;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 活动到期
 *
 * Class MemberExpired
 * @package app\common\command
 */
class ActivityExpired extends Command
{

    protected function configure()
    {
        $this
            ->setName('activity:expired')
            ->setDescription('活动到期');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("activity_expired command start");

        //找出过期未结束的活动
        $ids = model('app\common\model\Activity')
            ->where([
                'status' => Dict::ACTIVITY_STATUS_INPROGRESS,
                'end_time' => ['<', time()]
            ])
            ->column('id');
        if($ids) {
            try {
                //更新活动状态
                model('app\common\model\Activity')->allowField(true)->save([
                    'status' => Dict::ACTIVITY_STATUS_FINISH
                ], [
                    'status' => Dict::ACTIVITY_STATUS_INPROGRESS,
                    'end_time' => ['<', time()]
                ]);

                //更新用户参与状态
                model('app\common\model\ActivityUser')->allowField(true)->save([
                    'status' => Dict::ACTIVITY_USER_STATUS_FINISH
                ], [
                    'activity_id' => ['in', $ids],
                    'status'      => Dict::ACTIVITY_USER_STATUS_SIGN

                ]);
            } catch (\Exception $ex) {
                $output->writeln("activity_expired error: ". $ex->getTraceAsString());
            }
        }



        $output->writeln("activity_expired command end");
    }
}
