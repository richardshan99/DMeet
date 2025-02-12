<?php

namespace app\api\validate;

use think\Validate;

class Address extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name' => 'require',
        'mobile' => 'require',
        'province' => 'require',
        'city' => 'require',
        'region' => 'require',
        'address' => 'require',
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
        'add' => ['name', 'mobile', 'province', 'city', 'region', 'address'],
        'edit' => ['name', 'mobile', 'province', 'city', 'region', 'address'],

    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'name' => '收货人',
            'mobile' => '联系方式',
            'province' => '所在地区',
            'city' => '所在地区',
            'region' => '所在地区',
            'address' => '详细地址',
        ];
        parent::__construct($rules, $message, $field);
    }

}
