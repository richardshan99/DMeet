<?php

namespace app\common\model;

use app\common\library\Dict;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 * 门店扩展数据
 */
class ShopContent Extends Model
{

    protected $name = 'shop_content';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [

    ];

    protected $type = [
        "content_images" => "json"
    ];


    public function getContentImagesTextAttr($value, $row)
    {
        $images = $row['content_images'] ? json_decode($row['content_images'], true) : [];
        foreach($images as $key => &$item)
        {
            $item = cdnurl($item, true);
        }
        return $images;
    }
}
