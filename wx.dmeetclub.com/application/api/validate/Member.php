<?php

namespace app\api\validate;

use think\Validate;

class Member extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'id'       => 'require|number',
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
        'id.require'            => '请选择会员类型',
        'id.number'             => '请选择会员类型',
        'pay_type.require'      => '请选择支付方式',
        'pay_type.in'           => '请选择支付方式',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'pay' => ["id", "pay_type"]
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'id'        => '会员类型',
            'pay_type'  => '支付方式',
        ];
        parent::__construct($rules, $message, $field);
    }

}
