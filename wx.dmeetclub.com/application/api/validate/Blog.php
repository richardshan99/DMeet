<?php

namespace app\api\validate;

use think\Validate;

class Blog extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'invite_id' => 'require|number',
        'content' => 'require|max:500',
        'images'  => 'max:9',
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
        'invite_id.require' => '请选择要分享的邀约',
        'invite_id.number'  => '请选择要分享的邀约',
        'content.require' => '请填写动态内容',
        'content.max'     => '动态内容最多500字',
        'images.max'      => '动态图片最多上传9张',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'publish'   => ["content", "images"],
        'share'     => ["invite_id", "content", "images"],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'invite_id' => '邀约',
            'content'   => '动态内容',
            'images'    => '动态图片',
        ];
        parent::__construct($rules, $message, $field);
    }

}
