<?php

namespace app\common\model;

use app\common\library\Dict;
use think\Model;

/**
 * 门店类型
 */
class ShopCategory Extends Model
{

    protected $name = 'shop_category';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [
        "is_delete" => Dict::IS_FALSE
    ];

    protected $type = [

    ];

    /**
     * 获取列表
     */
    public function getList()
    {
        return $this->field('id,name')->where(['is_delete' => Dict::IS_FALSE])->select();
    }
}
