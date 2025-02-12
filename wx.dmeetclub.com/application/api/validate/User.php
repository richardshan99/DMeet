<?php

namespace app\api\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'avatar' => 'require',
        'nickname' => 'require|unique:user',
        'gender' => 'require|in:1,2',
        'height' => 'require',
        'school' => 'require',
        'education' => 'require',
        'birth' => 'require',
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
        'avatar.require' => '请上传头像',
        'nickname.require' => '请填写昵称',
        'nickname.unique' => '昵称已存在',
        'gender.require' => '请选择性别',
        'gender.in' => '请选择性别',
        'height.require' => '请填写身高',
        'school.require' => '请填写学校',
        'education.require' => '请选择学历',
        'birth.require' => '请选择生日',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'improve' => ["avatar", "nickname", "gender", "birth", "height", "school", "education", "birth"],

    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'avatar' => '头像',
            'nickname' => '昵称',
            'gender' => '头像',
            'height' => '身高',
            'school' => '学校',
            'education' => '学历',
            'birth' => '生日',
        ];
        parent::__construct($rules, $message, $field);
    }

}
