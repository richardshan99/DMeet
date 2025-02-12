<?php

namespace app\common\model;

use app\common\library\Dict;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 * 门店维护扩展数据
 */
class ShopChangeContent Extends Model
{

    protected $name = 'shop_change_content';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [

    ];

    protected $type = [
        "content_images" => "json"
    ];

    /**
     * 门店轮播图格式化
     * @param $value
     * @param $row
     * @return array|mixed
     */
    public function getContentImagesTextAttr($value, $row)
    {
        $images = $row['content_images'] ? json_decode($row['content_images'], true) : [];
        foreach ($images as $key => &$item)
        {
            $item = cdnurl($item, true);
        }
        return $images;
    }


}
