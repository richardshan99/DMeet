<?php

namespace app\api\validate;

use think\Validate;

class UserCert extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name'          => 'require',
        'idcard'        => 'require',
        'school'        => 'require',
        'degree'        => 'require',
        'images'        => 'require',
        'company'       => 'require',
        'position'      => 'require',
        'trade_id'         => 'require',
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
        'name.require'      => '请填写姓名',
        'idcard.require'    => '请填写身份证号',
        'school.require'    => '请填写毕业院校',
        'degree.require'    => '请填写学位',
        'images.require'    => '请上传图片',
        'company.require'   => '请填写公司名称',
        'trade_id.require'     => '请选择行业',
        'position.require'  => '请填写职位',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'realname'  => ["name", "idcard"],
        'education' => ["school", "degree", "images"],
        'work' => ["company", "position", "images"],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'name'      => '姓名',
            'idcard'    => '身份证号',
            'school'    => '毕业院校',
            'degree'    => '学位',
            'images'    => '图片',
            'company'   => '公司名称',
            'trade_id'   => '行业',
            'position'  => '职位',

        ];
        parent::__construct($rules, $message, $field);
    }

}
