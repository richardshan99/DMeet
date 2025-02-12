<?php

namespace app\common\command;


use app\common\library\Dict;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Queue;

/**
 * 邀约召集到期
 *
 * Class MemberExpired
 * @package app\common\command
 */
class InvitationCallExpired extends Command
{

    protected function configure()
    {
        $this
            ->setName('invitation:call:expired')
            ->setDescription('邀约召集到期');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("invitation_call_expired command start");

        //找出过期未成功的邀约
        $ids = model('app\common\model\InviteCall')
            ->where([
                'status' => Dict::INVITE_CALL_STATUS_PROCESS,
                'create_time' => ['<', strtotime('-7 days')]
            ])
            ->column('id');

        try {
            foreach($ids as $key => $item) {
                Queue::push("app\job\CancelInvitationCall", ['id' => $item], 'invitationcall');
            }

        } catch (\Exception $ex) {
            $output->writeln("activity_expired error: ". $ex->getTraceAsString());
        }

        $output->writeln("activity_expired command end");
    }
}
