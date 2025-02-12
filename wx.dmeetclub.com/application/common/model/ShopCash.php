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
 * 门店管理 - 提现
 */
class ShopCash Extends Model
{

    protected $name = 'shop_cash';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = false;

    // 追加属性
    protected $append = [

    ];

    protected $insert = [
        "status" => Dict::SHOP_CASH_STATUS_WAIT,
        "is_pay" => Dict::IS_FALSE
    ];

    protected $type = [
    ];


    public function getImageAttr($value, $row)
    {
        return $row['image'] ? cdnurl($row['image'], true) : [];
    }

    public function getStatusTextAttr($value, $row)
    {
        return Dict::getShopCashStatus($row['status']);
    }

    public function getCreateTimeTextAttr($value, $row)
    {
        return $row['create_time'] ? date("Y-m-d H:i", $row['create_time']) : "";
    }

    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id')->setEagerlyType(0);
    }

    public function shop()
    {
        return $this->belongsTo('app\common\model\Shop', 'shop_id')->setEagerlyType(0);
    }

    public function getIsPayTextAttr($value, $row)
    {
        return $row['is_pay'] == Dict::IS_TRUE ? "已打款" : "未打款";
    }

    /**
     * 添加提现
     * @param array $shops
     * @param User $user
     * @param $data
     * @return ShopCash
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function generate($shops = [], User $user, $data)
    {

        $shop = Shop::get($shops['shop_id']);
        if($shop->balance < $data['price']) {
            throw new BaseException(['msg' => "余额不足，提现失败"]);
        }

        (new ShopBalance)->generate($shop, Dict::SHOP_BALANCE_TYPE_CASH_DECR, $data['price']);
        return self::create(array_merge($data, [
            "shop_id" => $shop->id,
            "user_id" => $user->id,
        ]), true);
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
            $item->visible(['price', 'reject_reason', 'status']);
            $item->append(['status_text', 'create_time_text']);
        }

        return $list;
    }


    /**
     * 审核
     * @param $type
     * @param $params
     * @return bool|false|int
     * @throws Exception
     */
    public function audit($type, $params)
    {
        try {
            if($type == 'approve') { //通过
                $result =  $this->auditApprove($params);
            } elseif($type == 'reject') { //驳回
                $result =  $this->auditReject($params);
            } else {
                throw new \Exception("不支持的类型");
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }


        return $result;
    }

    /**
     * 审核通过
     * @param $params
     * @return bool|string
     * @throws Exception
     */
    public function auditApprove($params)
    {
        try {
            $result = $this->allowField(true)->save(array_merge($params ?: [], [
                "status"     => Dict::SHOP_CASH_STATUS_APPROVE
            ]));
        } catch (ValidateException|PDOException|Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $result;

    }

    /**
     * 审核驳回
     * @param $params
     * @return bool|string
     */
    public function auditReject($params)
    {
        Db::startTrans();
        try {
            //更新记录
            $this->allowField(true)->save(array_merge($params ?: [], [
                "reject_reason" => $params['reject_reason'],
                "status"     => Dict::SHOP_CASH_STATUS_REJECT
            ]));

            //新增资金记录
            $shop = model('app\common\model\Shop')->get($this->shop_id);
            (new ShopBalance)->generate($shop, Dict::SHOP_BALANCE_TYPE_CASH_FAILURE_INCR, $this->price);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            return "操作失败";
        }

        return true;

    }
}
