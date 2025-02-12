<?php

namespace app\api\validate;

use think\Validate;

class Index extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'gift_no' => 'require',
        'name' => 'require',
        'idcard' => 'require',
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

    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'receive' => ['gift_no', 'name', 'idcard'],

    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'gift_no' => '礼物编号',
            'name' => '受诺人姓名',
            'idcard' => '受诺人身份证号',
        ];
        parent::__construct($rules, $message, $field);
    }

}
