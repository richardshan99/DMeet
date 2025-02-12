<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

return [
    'app\admin\command\Crud',
    'app\admin\command\Menu',
    'app\admin\command\Install',
    'app\admin\command\Min',
    'app\admin\command\Addon',
    'app\admin\command\Api',
    'app\common\command\ActivityExpired', //活动 - 结束
    'app\common\command\WaitConfirmedInvitationCancel',//邀约- 待确认 - 48H或过期 - 取消
    'app\common\command\InvitationCallExpired',//邀约召集 - 7Day - 召集失败
    'app\common\command\WaitMeetInvitationExpired',//邀约 - 待见面 - 1H双方未核销 - 取消
];
