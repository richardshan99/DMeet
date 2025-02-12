<?php

namespace app\common\library;

use app\common\library\pay\Wechat;
use app\common\model\Activity;
use app\common\model\ActivityUser;
use app\common\model\InviteCall;
use app\common\model\Payment;
use app\common\model\Shop;
use app\common\model\ShopBalance;
use app\common\model\User;
use app\common\model\UserBalance;
use app\common\model\UserChange;
use think\Db;
use think\Queue;

/**
 * 系统消息
 * Class Menu
 * @package app\common\library
 */
class Message
{
    /** @var string 用户信息审核通过 */
    const TYPE_USER_INFO_AUDIT_APPROVE = 'TYPE_USER_INFO_AUDIT_APPROVE';
    /** @var string 用户信息审核驳回 */
    const TYPE_USER_INFO_AUDIT_REJECT = 'TYPE_USER_INFO_AUDIT_REJECT';
    /** @var string 邀约取消 */
    const TYPE_INVITATION_CANCEL = 'TYPE_INVITATION_CANCEL';
    /** @var string 邀约失败 */
    const TYPE_INVITATION_REFUSE = 'TYPE_INVITATION_REFUSE';
    /** @var string 邀约召集失败 */
    const TYPE_INVITATION_CALL_FAILURE = 'TYPE_INVITATION_CALL_FAILURE';
    /** @var string 活动取消 */
    const TYPE_ACTIVITY_CANCEL = 'TYPE_ACTIVITY_CANCEL';

    protected $message = [
        self::TYPE_USER_INFO_AUDIT_APPROVE => '您的#info_type#审核通过。',
        self::TYPE_USER_INFO_AUDIT_REJECT => '您的#info_type#审核失败，原因：#reject_reason#。',
        self::TYPE_INVITATION_CANCEL => '您和“#invitee#”的邀约已取消。#refund_price#',
        self::TYPE_INVITATION_REFUSE => '您对“#invitee#”发起的邀约失败。#refund_price#',
        self::TYPE_INVITATION_CALL_FAILURE => '您发起的“#shop_name#”召集无人响应，召集失败。#refund_price#',
        self::TYPE_ACTIVITY_CANCEL => '您参与的“#activity_name#”活动取消。#refund_price#',
    ];

    protected $user;
    protected $invite;
    protected $role;

    /**
     * Message constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * 活动取消
     * @param ActivityUser $activityUser
     * @throws \think\exception\DbException
     */
    public function cancelActivity(ActivityUser $activityUser)
    {
        $activity = Activity::get($activityUser->activity_id);
        $refundPriceStr = "";
        if($activityUser->price > 0) {
            if($activityUser->pay_type == Dict::PAY_TYPE_WECHAT) {
                $refundPriceStr .= "退款成功，退款金额为¥".$activityUser->price;
            }

            if($activityUser->pay_type == Dict::PAY_TYPE_BALANCE) {
                $refundPriceStr .= "退款成功，退还余额为¥".$activityUser->price;
            }
        }

        \app\common\model\MessageSystem::create([
            'user_id' => $this->user->id,
            'message' => str_replace([
                '#activity_name#', "#refund_price#"
            ], [
                $activity->name, $refundPriceStr
            ], $this->message[self::TYPE_ACTIVITY_CANCEL])
        ], true);
    }

    /**
     * 召集失败
     * @param Payment $payment
     * @param InviteCall $call
     * @throws \think\exception\DbException
     */
    public function cancelInvitationCall(Payment $payment, InviteCall $call)
    {
        $shop = Shop::get($call->shop_id);
        $refundPriceStr = "";
        if($payment->price > 0) {
            if($payment->pay_type == Dict::PAY_TYPE_WECHAT) {
                $refundPriceStr .= "退款成功，退款金额为¥".$payment->price;
            }

            if($payment->pay_type == Dict::PAY_TYPE_BALANCE) {
                $refundPriceStr .= "退款成功，退还余额为¥".$payment->price;
            }
        }
        \app\common\model\MessageSystem::create([
            'user_id' => $this->user->id,
            'message' => str_replace([
                '#shop_name#', "#refund_price#"
            ], [
                $shop->name, $refundPriceStr
            ], $this->message[self::TYPE_INVITATION_CALL_FAILURE])
        ], true);
    }

    /**
     * 邀约取消
     * @param Payment $payment
     * @param int $compensationBalance
     * @param int $compensationWechat
     * @throws \think\exception\DbException
     */
    public function cancelInvitation(Payment $payment, $compensationBalance = 0, $compensationWechat = 0)
    {
        $canceledUser = User::get($payment->user_id);
        $refundPriceStr = "";
        if($payment->price > 0) {
            if($payment->pay_type == Dict::PAY_TYPE_WECHAT) {
                $refundPriceStr .= "退款成功，退款金额为¥".$payment->price;
            }

            if($payment->pay_type == Dict::PAY_TYPE_BALANCE) {
                $refundPriceStr .= "退款成功，退还余额为¥".$payment->price;
            }
        }
        \app\common\model\MessageSystem::create([
            'user_id' => $this->user->id,
            'message' => str_replace([
                '#invitee#', "#refund_price#"
            ], [
                $canceledUser->nickname,
                $refundPriceStr
                . ($compensationWechat > 0 ? ", 补偿金额¥".$compensationWechat : "")
                . ($compensationBalance > 0 ? ", 补偿余额¥".$compensationBalance : "")
            ], $this->message[self::TYPE_INVITATION_CANCEL])
        ], true);
    }

    /**
     * 邀约拒绝
     * @param Payment $payment
     * @throws \think\exception\DbException
     */
    public function refuseInvitation(Payment $payment)
    {
        $invitee = User::get($payment->user_id);
        $refundPriceStr = "";
        if($payment->price > 0) {
            if($payment->pay_type == Dict::PAY_TYPE_WECHAT) {
                $refundPriceStr .= "退款成功，退款金额为¥".$payment->price;
            }

            if($payment->pay_type == Dict::PAY_TYPE_BALANCE) {
                $refundPriceStr .= "退款成功，退还余额为¥".$payment->price;
            }
        }
        \app\common\model\MessageSystem::create([
            'user_id' => $this->user->id,
            'message' => str_replace([
                '#invitee#', "#refund_price#"
            ], [
                $invitee->nickname, $refundPriceStr
            ], $this->message[self::TYPE_INVITATION_REFUSE])
        ], true);
    }

    /**
     * 个人资料审核成功
     * @param UserChange $userChange
     */
    public function auditApproveUserInfo(UserChange $userChange)
    {
        $changeType = [];
        if($userChange->avatar)  {
            $changeType[] = '头像';
        }
        if($userChange->nickname)  {
            $changeType[] = '昵称';
        }
        if($userChange->intro)  {
            $changeType[] = '个人介绍';
        }
        \app\common\model\MessageSystem::create([
            'user_id' => $this->user->id,
            'message' => str_replace([
                '#info_type#'
            ], [
                implode(" ", $changeType)
            ], $this->message[self::TYPE_USER_INFO_AUDIT_APPROVE])
        ], true);
    }

    /**
     * 个人资料审核驳回
     * @param UserChange $userChange
     */
    public function auditRejectUserInfo(UserChange $userChange)
    {
        $changeType = [];
        if($userChange->avatar)  {
            $changeType[] = '头像';
        }
        if($userChange->nickname)  {
            $changeType[] = '昵称';
        }
        if($userChange->intro)  {
            $changeType[] = '个人介绍';
        }
        \app\common\model\MessageSystem::create([
            'user_id' => $this->user->id,
            'message' => str_replace([
                '#info_type#', "#reject_reason#"
            ], [
                implode(" ", $changeType), $userChange->reject_reason
            ], $this->message[self::TYPE_USER_INFO_AUDIT_REJECT])
        ], true);
    }
}
