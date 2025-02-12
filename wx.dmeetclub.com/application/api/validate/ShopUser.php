<?php

namespace app\api\validate;

use think\Validate;

class ShopUser extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'mobile' => 'require',
        'name' => 'require',
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
        'add'   => ["mobile", "name"],
        'edit'   => ["mobile", "name"],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'mobile' => '手机号',
            'name'   => '姓名',
        ];
        parent::__construct($rules, $message, $field);
    }

}
