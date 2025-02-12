<?php

namespace app\api\validate;

use think\Validate;

class BlogReport extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'content' => 'require',
        'images'  => 'max:3',
        'blog_id' => 'require'
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
        'content.require' => '请填写动态内容',
        'images.max'      => '动态图片最多上传3张',
        'blog_id.require' => '动态不存在',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'publish' => ["content", "images"]
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'content' => '动态内容',
            'images' => '动态图片',
        ];
        parent::__construct($rules, $message, $field);
    }

}
