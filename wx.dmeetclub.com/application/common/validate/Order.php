<?php

namespace app\api\validate;

use think\Validate;

class Order extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'buyer_name' => 'require',
        'buyer_mobile' => 'require',
        'buyer_idcard' => 'require',
        'promisee_name' => 'require',
        'promisee_idcard' => 'require',
        'goods_id' => 'require',
        'address_id' => 'require',
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
        "goods_id.require" => "请选择购买商品",
        "address_id.require" => "请选择收货地址",
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'pay' => ['buyer_name', 'buyer_mobile', 'buyer_idcard', 'promisee_name', 'promisee_idcard', 'goods_id', 'address_id'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'buyer_name' => '承诺人姓名',
            'buyer_mobile' => '承诺人手机号',
            'buyer_idcard' => '承诺人身份证号码',
            'promisee_name' => '受诺人姓名',
            'promisee_idcard' => '受诺人身份证号码',
            'goods_id' => '商品',
            'address_id' => '地址',
        ];
        parent::__construct($rules, $message, $field);
    }

}
