<?php

namespace app\api\validate;

use think\Validate;

class Invitation extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'invite_user_id' => 'require|number',
        'shop_id'       => 'require|number',
        'package'  => 'require|in:1,2',
        'meet_time'  => 'require',
        'pay_mode'  => 'require|in:1,2,3',
        'pay_type'  => 'require|in:1,2',
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'invite_user_id.require' => '请选择邀约用户',
        'invite_user_id.number'  => '请选择邀约用户',
        'shop_id.require'       => '请选择见面门店',
        'shop_id.number'        => '请选择见面门店',
        'package.require'       => '请选择套餐',
        'package.in'            => '请选择套餐',
        'meet_time.require'     => '请选择见面时间',
        'pay_mode.require'      => '请选择付费方式',
        'pay_mode.in'           => '请选择付费方式',
        'pay_type.require'      => '请选择支付方式',
        'pay_type.in'           => '请选择支付方式',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add' => ["content", "images"]
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'invite_usr_id' => '被邀请用户id',
            'shop_id'       => '门店id',
            'package'  => '套餐',
            'meet_time'  => '见面时间',
            'pay_mode'  => '付费方式',
            'pay_type'  => '支付方式',
        ];
        parent::__construct($rules, $message, $field);
    }

}
