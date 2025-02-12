<?php

namespace app\common\model;

use app\common\library\Dict;
use think\Db;
use think\Model;

/**
 * 我的见面红包管理
 */
class UserRedEnvelopeBalance Extends Model
{

    protected $name = 'user_meeting_red_envelope_balance';
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
        return Dict::getUserRedBalanceType($row['flow_type']);
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
                Dict::USER_RED_BALANCE_2
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
     * 生成见面红包记录
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
            if(in_array($type, [Dict::USER_RED_BALANCE_2])) {
                $isRet = true;
                $user->setDec('red_envelope_balance', $price);
            }

            if(in_array($type, [
                Dict::USER_RED_BALANCE_1,
                Dict::USER_RED_BALANCE_3,
            ])) {
                $user->setInc('red_envelope_balance', $price);
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
