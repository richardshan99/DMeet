<?php

namespace app\common\command;


use app\common\library\Dict;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 待见面邀约超时取消 -- 1H
 *
 * Class WaitMeetInvitationExpired
 * @package app\common\command
 */
class WaitMeetInvitationExpired extends Command
{

    protected function configure()
    {
        $this
            ->setName('wait:meet:invitation:expired')
            ->setDescription('待见面邀约超时取消');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("wait_meet_invitation command start");

        //找出过期双方未见面的活动
        $ids = model('app\common\model\Invite')
            ->where([
                'status' => Dict::INVITE_STATUS_WAIT_MEET,
                'inviter_is_verify' => Dict::IS_FALSE,
                'invitee_is_verify' => Dict::IS_FALSE,
                'meet_time' => ['<', bcsub(time(), 3600, 0)]
            ])
            ->column('id');
        if($ids) {
            try {
                //更新邀约状态
                model('app\common\model\Invite')->allowField(true)->save([
                    'status' => Dict::INVITE_STATUS_CANCEL
                ], [
                    'id' => ['in', $ids],
                    'status' => Dict::INVITE_STATUS_WAIT_MEET,
                ]);

            } catch (\Exception $ex) {
                $output->writeln("wait_meet_invitation error: ". $ex->getTraceAsString());
            }
        }



        $output->writeln("wait_meet_invitation command end");
    }
}
