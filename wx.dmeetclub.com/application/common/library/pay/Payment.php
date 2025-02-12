<?php


namespace app\common\library\pay;


use app\common\exception\BaseException;
use app\common\library\Dict;
use app\common\library\Message;
use app\common\model\Activity;
use app\common\model\ActivityUser;
use app\common\model\Invite;
use app\common\model\InviteCall;
use app\common\model\InviteLog;
use app\common\model\Member;
use app\common\model\User;
use DI\CompiledContainer;

class Payment
{

    /** @var 支付方式 */
    protected $payType;
    /** @var 错误信息 */
    protected $error;
    /** @var User $model */
    protected $user;

    /**
     * Payment constructor.
     * @param $user
     * @param null $payType
     */
    public function __construct($user, $payType = null)
    {
        $this->user = $user;
        $this->payType = $payType;
    }

    /**
     * @param null $payType
     */
    public function setPayType($payType = null)
    {
        $this->payType = $payType;
    }

    /**
     * @return 支付方式|null
     */
    public function getPayType()
    {
        return $this->payType;
    }

    /**
     * 创建支付 -- 活动
     * @param Activity $activity
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function activity(Activity $activity)
    {
        $result = "false";
        $payment = "";
        $message = "";

        switch ($this->payType) {
            case Dict::PAY_TYPE_WECHAT:
                $payment = (new Wechat($this->user))->joinActivity($activity);
                break;
            case Dict::PAY_TYPE_BALANCE:
                $balance = new Balance($this->user);
                $result = $balance->joinActivity($activity);
                $message = "支付成功";
                if ($result === false) {
                    $message = $balance->getError();
                }
                break;
            default:
                throw new \Exception("没有匹配的支付方式");
        }

        return array(
            "result" => $result,
            "message" => $message,
            "payment" => $payment,
            "pay_type" => $this->payType,
        );
    }

    /**
     * 创建支付 -- 邀约
     * @param InviteLog $inviteLog
     * @return array
     * @throws \Exception
     */
    public function invitation(InviteLog $inviteLog)
    {
        $result = "false";
        $payment = "";
        $message = "";

        switch ($this->payType) {
            case Dict::PAY_TYPE_WECHAT:
                $payment = (new Wechat($this->user))->invitation($inviteLog);
                break;
            case Dict::PAY_TYPE_BALANCE:
                $balance = new Balance($this->user);
                $result = $balance->invitation($inviteLog);
                $message = "支付成功";
                if ($result === false) {
                    $message = $balance->getError();
                }
                break;
            default:
                throw new \Exception("没有匹配的支付方式");
        }

        return array(
            "result" => $result,
            "message" => $message,
            "payment" => $payment,
            "pay_type" => $this->payType,
        );
    }

    /**
     * 同意邀约
     * @param Invite $invite
     * @return array
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function approveInvitation(Invite $invite)
    {
        $result = "false";
        $payment = "";
        $message = "";

        switch ($this->payType) {
            case Dict::PAY_TYPE_WECHAT:
                $payment = (new Wechat($this->user))->approveInvitation($invite);
                break;
            case Dict::PAY_TYPE_BALANCE:
                $balance = new Balance($this->user);
                $result = $balance->approveInvitation($invite);
                $message = "支付成功";
                if ($result === false) {
                    $message = $balance->getError();
                }
                break;
            default:
                throw new \Exception("没有匹配的支付方式");
        }

        return array(
            "result" => $result,
            "message" => $message,
            "payment" => $payment,
            "pay_type" => $this->payType,
        );
    }

    /**
     * 见面 - 发起召集
     * @param InviteCall $inviteCall
     * @return array
     * @throws \Exception
     */
    public function call(InviteCall $inviteCall)
    {
        $result = "false";
        $payment = "";
        $message = "";

        switch ($this->payType) {
            case Dict::PAY_TYPE_WECHAT:
                $payment = (new Wechat($this->user))->call($inviteCall);
                break;
            case Dict::PAY_TYPE_BALANCE:
                $balance = new Balance($this->user);
                $result = $balance->call($inviteCall);
                $message = "支付成功";
                if ($result === false) {
                    $message = $balance->getError();
                }
                break;
            default:
                throw new \Exception("没有匹配的支付方式");
        }

        return array(
            "result" => $result,
            "message" => $message,
            "payment" => $payment,
            "pay_type" => $this->payType,
        );
    }

    /**
     * 购买会员
     * @param Member $member
     * @return array
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Exception
     */
    public function buyMember(Member $member)
    {
        $result = "false";
        $payment = "";
        $message = "";

        switch ($this->payType) {
            case Dict::PAY_TYPE_WECHAT:
                $payment = (new Wechat($this->user))->buyMember($member);
                break;
            case Dict::PAY_TYPE_BALANCE:
                $balance = new Balance($this->user);
                $result = $balance->buyMember($member);
                $message = "支付成功";
                if ($result === false) {
                    $message = $balance->getError();
                }
                break;
            default:
                throw new \Exception("没有匹配的支付方式");
        }

        return array(
            "result" => $result,
            "message" => $message,
            "payment" => $payment,
            "pay_type" => $this->payType,
        );
    }

    /**
     * 活动退款
     * @param ActivityUser $activityUser
     * @return bool
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Exception
     */
    public function refundActivity(ActivityUser $activityUser)
    {
        //查找付款记录
        $payment = model('app\common\model\Payment')->where([
            "user_id" => $this->user->id,
            "service_type" => Dict::SERVICE_TYPE_ACTIVITY,
            "service_biz_id" => $activityUser->activity_id,
            "is_refund" => Dict::IS_FALSE
        ])->find();
        if (!$payment) {
            throw new BaseException(['msg' => "您无法退款"]);
        }

        $activity = $activityUser->activity;
        //去除秒
        $beginTime = strtotime(date("Y-m-d H:i", $activity->begin_time));
        $currentTime = strtotime(date("Y-m-d H:i", time()));
        //判断活动距离开始的时间
        $diffHour = bcdiv(bcsub($beginTime, $currentTime), 3600, 2);
        //计算退费金额
        if ($diffHour >= 48) {//48小时，全额退款
            $refund = $activityUser->price;
        } elseif ($diffHour >= 24) { //24小时，退50%
            $refund = bcdiv($activityUser->price, 2, 2);
        } else { //24小时内，不退
            $refund = 0;
        }

        $result = false;
        if ($refund > 0) { //退款金额大于0
            switch ($activityUser->pay_type) {
                case Dict::PAY_TYPE_WECHAT:
                    $result = (new Wechat($this->user))->refundActivity($payment, $refund);
                    break;
                case Dict::PAY_TYPE_BALANCE:
                    $result = (new Balance($this->user))->refundActivity($payment, $refund);
                    break;
                default:
                    throw new BaseException(['msg' => "没有匹配的支付方式"]);
            }
        }

        if ($result !== false || $refund === 0) {
            try {
                $payment->save([
                    'is_refund' => DIct::IS_TRUE,
                    'refund_time' => time(),
                    'refund_price' => $refund
                ]);
                //变更参与状态为已退费
                $activityUser->save([
                    'status' => Dict::ACTIVITY_USER_STATUS_REFUND,
                    'refund_time' => time(),
                    'refund_price' => $refund
                ]);
            } catch (\Exception $ex) {
                return false;
            }

            return true;
        }

        return false;

    }

    /**
     * 后台-活动取消
     * @param ActivityUser $activityUser
     * @return bool
     * @throws BaseException
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refundActivityBackend(ActivityUser $activityUser)
    {
        //查找付款记录
        $payment = model('app\common\model\Payment')->where([
            "user_id" => $this->user->id,
            "service_type" => Dict::SERVICE_TYPE_ACTIVITY,
            "service_biz_id" => $activityUser->activity_id,
            "is_refund" => Dict::IS_FALSE
        ])->find();
        if (!$payment) {
            throw new BaseException(['msg' => "您无法退款"]);
        }

        switch ($activityUser->pay_type) {
            case Dict::PAY_TYPE_WECHAT:
                $result = (new Wechat($this->user))->refundActivity($payment, $payment->price);
                break;
            case Dict::PAY_TYPE_BALANCE:
                $result = (new Balance($this->user))->refundActivity($payment, $payment->price);
                break;
            default:
                throw new \Exception("没有匹配的支付方式");
        }

        try {
            $payment->save([
                'is_refund' => DIct::IS_TRUE,
                'refund_time' => time(),
                'refund_price' => $payment->price
            ]);
            //变更参与状态为已退费
            $activityUser->save([
                'status' => Dict::ACTIVITY_USER_STATUS_REFUND,
                'refund_time' => time(),
                'refund_price' => $payment->price
            ]);
        } catch (\Exception $ex) {
            return false;
        }
        return true;
    }

    /**
     * 活动召集退款
     * @param InviteCall $call
     * @return bool
     * @throws BaseException
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refundInvitationCall(InviteCall $call)
    {
        //查找付款记录
        $payment = model('app\common\model\Payment')->where([
            "user_id" => $this->user->id,
            "service_type" => Dict::SERVICE_TYPE_INVITE_CALL,
            "service_biz_id" => $call->id,
            "is_refund" => Dict::IS_FALSE
        ])->find();
        if (!$payment) {
            throw new BaseException(['msg' => "您无法退款"]);
        }

        //退款
        (new Wechat($this->user))->refundInvitationCall($payment, $payment->price);

        try {
            $payment->save([
                'is_refund' => DIct::IS_TRUE,
                'refund_time' => time(),
                'refund_price' => $payment->price
            ]);
            //变更参与状态为已退费
            $call->save(['status' => Dict::INVITE_CALL_STATUS_FAILURE]);
        } catch (\Exception $ex) {
            return false;
        }

        try {
            $library = new Message($this->user);
            $library->cancelInvitationCall($payment, $call);
        } catch (\Exception $ex) {}
        return true;
    }

    public function setError($message = "")
    {
        $this->error = $message;
    }

    public function getError()
    {
        return $this->error;
    }
}