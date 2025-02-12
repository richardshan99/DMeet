<?php

namespace app\common\model;

use app\common\library\Dict;
use think\Db;
use think\Model;

/**
 * 我的余额管理
 */
class UserBalance Extends Model
{

    protected $name = 'user_balance';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    public function getTypeTextAttr($value, $row)
    {
        return Dict::getUserBalanceType($row['flow_type']);
    }

    public function getCreateTimeTextAttr($value, $row)
    {
        return $row['create_time'] ? date("Y-m-d H:i", $row['create_time']) : "";
    }

    public function getList(User $user)
    {
        $list =  $this->where('user_id', $user->id)->order('create_time', 'desc')->paginate(20);
        foreach($list as $key => $item)
        {
            $symbol = '+';
            if(in_array($item->flow_type, [
                Dict::USER_BALANCE_TYPE_PAY_DECR
            ])) {
                $symbol = '-';
            }
            $item->price = $symbol.$item->price;
            $item->visible(['price', 'type']);
            $item->append(['type_text', 'create_time_text']);
        }

        return $list;
    }

    /**
     * 生成余额记录
     * @param User $user
     * @param $type 类型
     * @param $price 金额
     * @return bool
     * @throws \think\Exception
     */
    public function generate(User $user, $type, $price)
    {
        $isRet = false;
        try {
            //减少 -- 支付
            if(in_array($type, [Dict::USER_BALANCE_TYPE_PAY_DECR])) {
                $isRet = true;
                $user->setDec('balance', $price);
            }

            //增加 -- 邀请取消 / 邀请分享 / 活动退款
            if(in_array($type, [
                Dict::USER_BALANCE_TYPE_INVITE_INCR,
                Dict::USER_BALANCE_TYPE_INVITE_SHARE_INCR,
                Dict::USER_BALANCE_TYPE_ACTIVITY_REFUND_INCR
            ])) {
                $user->setInc('balance', $price);
                $isRet = true;
            }

            if($isRet) {
                $user->save();

                //生成记录
                self::create([
                    "user_id"   => $user->id,
                    "flow_type" => $type,
                    "price"     => $price
                ],true);
            }
        } catch (\Exception $ex) {
            return false;
        }
        return $isRet;
    }
}
