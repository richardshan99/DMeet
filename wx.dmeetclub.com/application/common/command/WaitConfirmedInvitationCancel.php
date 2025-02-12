<?php

namespace app\common\command;


use app\common\library\Dict;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Queue;

/**
 * 邀约- 待确认 - 48H或过期 - 取消
 *
 * Class MemberExpired
 * @package app\common\command
 */
class WaitConfirmedInvitationCancel extends Command
{

    protected function configure()
    {
        $this
            ->setName('wait:confirm:invitation:cancel')
            ->setDescription('邀约-待确认-48H未处理');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("wait_confirm_invitation_cancel command start");

        //找出超出48小时未确认的邀约
        $invites = model('app\common\model\Invite')
            ->where([
                'status' => Dict::INVITE_STATUS_WAIT_CONFIRM,
                'create_time' => ['<', strtotime('-48 hours', time())]
            ])
            ->select();
        $inviteArr = collection($invites)->toArray();
        foreach($inviteArr as $key => $item) {
            Queue::push("app\job\CancelInvitation", $item, 'invitation');
        }

        $output->writeln("wait_confirm_invitation_cancel command end");
    }
}
