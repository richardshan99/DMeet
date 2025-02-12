<?php

namespace app\common\model;

use app\common\exception\BaseException;
use app\common\library\Dict;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 * 门店金额记录
 */
class ShopBalance Extends Model
{

    protected $name = 'shop_balance';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = false;

    // 追加属性
    protected $append = [

    ];

    protected $insert = [

    ];

    protected $type = [
    ];

    public function getBalanceTypeTextAttr($value, $row)
    {
        return Dict::getShopBalanceType($row['balance_type']);
    }

    public function getCreateTimeTextAttr($value, $row)
    {
        return $row['create_time'] ? date("Y-m-d H:i", $row['create_time']) : "";
    }
    /**
     * 添加流水
     * @param Shop $shop
     * @param $type
     * @param $data
     * @return void
     * @throws \think\Exception
     */
    public function generate(Shop $shop, $type, $price)
    {
        if($type == Dict::SHOP_BALANCE_TYPE_REVENUE_INCR) {//营收增加
            $shop->setInc('balance', $price);
        }

        if($type == Dict::SHOP_BALANCE_TYPE_CASH_DECR) {//提现成功减少
            $shop->setDec('balance', $price);
        }

        if($type == Dict::SHOP_BALANCE_TYPE_CASH_FAILURE_INCR) {//提现失败返还增加
            $shop->setInc('balance', $price);
        }

        $shop->save();

        return self::create([
            "shop_id"      => $shop->id,
            "balance_type" => $type,
            "price"        => $price,
        ]);
    }

    /**
     * 获取列表
     * @param array $shops
     * @return array
     * @throws \think\exception\DbException
     */
    public function getList($shops = [])
    {
        if($shops['has_shop'] == false) return [];
        $shop = Shop::get($shops['shop_id']);
        $list = $this->where('shop_id', $shop->id)->order('create_time', 'desc')->paginate(20);
        foreach($list as $key => $item)
        {
            $symbol = "+";
            if($item->balance_type == Dict::SHOP_BALANCE_TYPE_CASH_DECR) { //提现减少
                $symbol = "-";
            }
            $item->price = $symbol . $item->price;
            $item->visible(['price']);
            $item->append(['balance_type_text', 'create_time_text']);
        }

        return $list;
    }
}
