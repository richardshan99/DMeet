<?php

namespace app\api\validate;

use think\Validate;

class Shop extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name'          => 'require',
        'mobile'        => 'require',
        'area_id'       => 'require',
        'position'      => 'require',
        'address'       => 'require',
        'shop_name'     => 'require',
        'shop_category_id'     => 'require',
        'business_license_image'      => 'require',
        'other_images'      => 'require|max:3',
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
        'name.require'          => '请填写姓名',
        'mobile.require'        => '请填写手机号码',
        'area_id.require'       => '请填写地区',
        'position.require'      => '请选择定位',
        'address.require'       => '请填写详细地址',
        'shop_name.require'     => '请填写门店名称',
        'shop_category_id.require'     => '请选择门店类别',
        'business_license_image.require'     => '营业执照不能为空',
        'other_images.require'     => '请上传其他图片',
        'other_images.max'      => '其他图片最多上传3张',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'apply' =>  [

        ]
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'name'          => '姓名',
            'mobile'        => '手机号码',
            'area_id'       => '地区',
            'position'      => '定位',
            'address'       => '详细地址',
            'shop_name'     => '门店名称',
            'shop_category_id'     => '门店类别',
            'business_license_image'     => '营业执照',
            'other_images'     => '其他图片',

        ];
        parent::__construct($rules, $message, $field);
    }

}
