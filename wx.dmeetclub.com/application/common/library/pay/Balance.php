<?php


namespace app\common\library\pay;


use app\common\library\Dict;
use app\common\library\ProjectPermission;
use app\common\model\Activity;
use app\common\model\Invite;
use app\common\model\InviteCall;
use app\common\model\InviteLog;
use app\common\model\Member;
use app\common\model\User;
use app\common\model\UserBalance;
use think\Db;
use Yansongda\Pay\Pay;

/**
 * 余额支付
 *
 * Class Points
 * @package app\common\library\pay
 */
class Balance extends Base
{
    /** @var User  */
    protected $user;

    protected $error;

    /**
     * Points constructor.
     * @param User $user
     * @param Activity $activity
     */
    public function __construct(User $user)
    {
        $this->user     = $user;
    }

    /**
    * 参加活动
    * @param Activity $activity
    * @return bool
    */
    public function joinActivity(Activity $activity)
    {
        //判断余额是否够付
        if($this->user->balance < $activity->price) {
            $this->setError("余额不足");
            return false;
        }

        Db::startTrans();
        try {
            //生成财务记录
            $payment = $this->createPayment(
                $this->user,
                Dict::SERVICE_TYPE_ACTIVITY,
                Dict::PAY_TYPE_BALANCE,
                $activity->price,
                $activity
            );

            //操作积分
            $payResult = (new UserBalance)->generate($this->user, Dict::USER_BALANCE_TYPE_PAY_DECR, $payment->price);
            if($payResult !== true) {
                throw new \Exception('支付失败');
            }

            //支付成功，参与活动
            if(true !== $this->createActivityUser($payment)) {
                throw new \Exception('支付失败');
            }


            Db::commit();
        } catch (\Exception $ex) {
            Db::rollback();
            $this->setError("支付失败");
            return false;
        }

        return true;
    }

    /**
     * 邀约
     * @param InviteLog $inviteLog
     * @return bool
     */
    public function invitation(InviteLog $inviteLog)
    {
        //判断余额是否够付
        if($this->user->balance < $inviteLog->inviter_paid) {
            $this->setError("余额不足");
            return false;
        }

        Db::startTrans();
        try {
            //生成财务记录
            $payment = $this->createPayment(
                $this->user,
                Dict::SERVICE_TYPE_INVITATION,
                Dict::PAY_TYPE_BALANCE,
                $inviteLog->inviter_paid,
                $inviteLog
            );

            //支付
            $payResult = (new UserBalance)->generate($this->user, Dict::USER_BALANCE_TYPE_PAY_DECR, $payment->price);
            if($payResult !== true) {
                throw new \Exception('支付失败');
            }

            //付款成功
            if(true !== $this->createInvitation($payment)) {
                throw new \Exception('支付失败');
            }
            Db::commit();
        } catch (\Exception $ex) {
            Db::rollback();
            $this->setError("支付失败");
            return false;
        }

        return true;
    }

    /**
     * 同意邀约
     * @param Invite $invite
     * @return bool
     */
    public function approveInvitation(Invite $invite)
    {
        //判断余额是否够付
        if($this->user->balance < $invite->invitee_paid) {
            $this->setError("余额不足");
            return false;
        }

        Db::startTrans();
        try {
            //生成财务记录
            $payment = $this->createPayment(
                $this->user,
                Dict::SERVICE_TYPE_APPROVE_INVITATION,
                Dict::PAY_TYPE_BALANCE,
                $invite->invitee_paid,
                $invite
            );

            //支付
            $payResult = (new UserBalance)->generate($this->user, Dict::USER_BALANCE_TYPE_PAY_DECR, $payment->price);
            if($payResult !== true) {
                throw new \Exception('支付失败');
            }

            //同意邀约
            if(true !== $this->createApproveInvitation($payment)) {
                throw new \Exception('支付失败');
            }

            Db::commit();
        } catch (\Exception $ex) {
            Db::rollback();
            $this->setError("支付失败");
            return false;
        }

        return true;
    }

    /**
     * 邀约
     * @param InviteCall $inviteCall
     * @return bool
     */
    public function call(InviteCall $inviteCall)
    {
        $totalPrice = bcadd($inviteCall->inviter_paid, $inviteCall->publish_fee, 2);
        //判断余额是否够付
        if($this->user->balance < $totalPrice) {
            $this->setError("余额不足");
            return false;
        }

        Db::startTrans();
        try {
            //生成财务记录
            $payment = $this->createPayment(
                $this->user,
                Dict::SERVICE_TYPE_INVITE_CALL,
                Dict::PAY_TYPE_BALANCE,
                $totalPrice,
                $inviteCall
            );

            //支付
            $payResult = (new UserBalance)->generate($this->user, Dict::USER_BALANCE_TYPE_PAY_DECR, $payment->price);
            if($payResult !== true) {
                throw new \Exception('支付失败');
            }

            //付款成功
            if(true !== $this->createInviteCall($payment)) {
                throw new \Exception('发起召集失败');
            }
            Db::commit();
        } catch (\Exception $ex) {
            Db::rollback();
            $this->setError("支付失败");
            return false;
        }

        return true;
    }

    /**
     * 参加活动
     * @param Member $member
     * @return bool
     */
    public function buyMember(Member $member)
    {
        //判断余额是否够付
        if($this->user->balance < $member->price) {
            $this->setError("余额不足");
            return false;
        }

        Db::startTrans();
        try {
            //生成财务记录
            $payment = $this->createPayment(
                $this->user,
                Dict::SERVICE_TYPE_BUY_MEMBER,
                Dict::PAY_TYPE_BALANCE,
                $member->price,
                $member
            );

            //操作积分
            $payResult = (new UserBalance)->generate($this->user, Dict::USER_BALANCE_TYPE_PAY_DECR, $payment->price);
            if($payResult !== true) {
                throw new \Exception('支付失败');
            }

            //支付成功，购买会员
            if(true !== $this->createMember($payment)) {
                throw new \Exception('支付失败');
            }
            Db::commit();
        } catch (\Exception $ex) {
            Db::rollback();
            $this->setError("支付失败");
            return false;
        }

        return true;
    }

    /**
     * 活动 - 退款
     * @param $payment
     * @param $refund
     * @return bool
     * @throws \think\Exception
     */
    public function refundActivity($payment, $refund)
    {
        $user = User::get($payment->user_id);
        //操作积分
        return (new UserBalance)->generate($user, Dict::USER_BALANCE_TYPE_ACTIVITY_REFUND_INCR, $refund);

    }

    public function setError($error = "") {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }
}