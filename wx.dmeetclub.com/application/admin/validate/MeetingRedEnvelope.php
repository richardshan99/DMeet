<?php

namespace app\admin\validate;

use think\Validate;

class MeetingRedEnvelope extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'price' => 'require|number|gt:0',
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'price.require' => '金额不能为空',
        'price.number' => '金额需要是数字',
        'price.gt' => '金额需要大于0',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [ 'price'],
    ];

}
