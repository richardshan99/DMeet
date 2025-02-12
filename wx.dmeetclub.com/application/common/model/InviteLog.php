<?php

namespace app\common\model;

use app\common\library\Dict;
use app\common\library\pay\Payment;
use fast\Tree;
use think\Model;
use think\Config;

/**
 * 邀约待支付表
 */
class InviteLog Extends Model
{

    protected $name = 'invite_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;

    protected $insert = [
    ];

    protected $type = [
        "package" => 'json',
    ];

    /**
     * 生成邀约待支付记录
     * @param User $user
     * @param $params
     * @return InviteLog
     * @throws \Exception
     */
    public function generate(User $user, $params)
    {
        if($params['shop_type'] == Dict::SHOP_TYPE_NO_RESTAURANT){  // 非餐厅类型 支付金额
            $deposit = Config::get('site.new_deposit_price');
            $packagePrice = $params['meeting_red_envelope_price'] + $deposit;
            $bisectDeposit = bcdiv($deposit,2,2);
            $inviter_paid = bcadd($bisectDeposit , $params['meeting_red_envelope_price'],2); // 邀请人 履约金/2+红包
            $invitee_paid = $bisectDeposit; // 被邀请人 履约金/2
        }else{
            $packagePrice = (string)$params['package']['price'];
            $deposit = bcdiv($packagePrice, 2, 2);

            $inviter_paid = $invitee_paid = 0;
            if($params['pay_mode'] == Dict::INVITE_PAY_MODE_MY) {// 我付
                $inviter_paid = $packagePrice; //邀请人 - 餐费
                $invitee_paid = $deposit;//被邀请人 - 履约金
            }

            if($params['pay_mode'] == Dict::INVITE_PAY_MODE_YOU) {// 你付
                $invitee_paid = $packagePrice; //被邀请人 - 餐费
                $inviter_paid = $deposit;//邀请人 - 履约金
            }

            if($params['pay_mode'] == Dict::INVITE_PAY_MODE_HALF) {// AA
                $inviter_paid = $invitee_paid = $deposit; //被邀请人/邀请人 - （餐费/2 = 履约金）
                $deposit = 0; //AA无需 履约金
            }
        }

        try {
            $result = self::create(array_merge($params, [
                "user_id" => $user->id,
                "price"   => $packagePrice,
                "inviter_paid" => $inviter_paid,
                "invitee_paid" => $invitee_paid,
                "deposit" => $deposit,
                'meeting_red_envelope_price' => $params['meeting_red_envelope_price'],
                "deposit_pay_type" => $params['pay_mode'] == Dict::INVITE_PAY_MODE_YOU ? $params['pay_type'] : null,
            ]), true);

            if($result === false) {
                throw new \Exception("邀约失败");
            }

            //付款
            $payment = new Payment($user, $params['pay_type']);
            $result = $payment->invitation($result);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

        return $result;
    }
}
