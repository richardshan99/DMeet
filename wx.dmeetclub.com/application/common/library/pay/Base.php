<?php
namespace app\common\library\pay;


use app\common\library\Commission;
use app\common\library\Dict;
use app\common\library\ProjectPermission;
use app\common\model\Activity;
use app\common\model\ActivityUser;
use app\common\model\BalanceLog;
use app\common\model\Config;
use app\common\model\Invite;
use app\common\model\InviteCall;
use app\common\model\InviteLog;
use app\common\model\InvitePromotion;
use app\common\model\Member;
use app\common\model\MessageInvitation;
use app\common\model\Payment;
use app\common\model\PointLog;
use app\common\model\ProjectCostFLow;
use app\common\model\ProjectFileSignment;
use app\common\model\ProjectFinance;
use app\common\model\ProjectStaff;
use app\common\model\ProjectUserQuestionBankLog;
use app\common\model\Shop;
use app\common\model\User;
use app\common\model\UserBalance;
use app\common\model\UserFollow;
use app\common\model\UserMember;
use DI\CompiledContainer;
use think\Db;
use Yansongda\Pay\Logger;
use Yansongda\Supports\Collection;

class Base
{
    /**
     * 生成活动参与记录
     * @param Payment|null $payment
     * @return bool
     */
    public function createActivityUser(Payment $payment = null)
    {
        try {
            //更新支付状态
            $payment->save(['is_use' => 1, 'notify_time' => time()]);

            $user = User::get($payment->user_id);
            //获取参与记录
            ActivityUser::create([
                "activity_id" => $payment->service_biz_id,
                "user_id"     => $user->id,
                "price"       => $payment->price,
                "pay_type"    => $payment->pay_type,
            ]);
        } catch (\Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * 创建邀约（待确认）
     * @param Payment|null $payment
     * @return bool
     */
    public function createInvitation(Payment $payment = null)
    {
        try {
            //更新支付状态
            $payment->save(['is_use' => 1, 'notify_time' => time()]);

            //获取邀约记录
            $inviteLog = InviteLog::get($payment->service_biz_id);
            $inviteLog->hidden(['id']);
            //获取门店
            $shop = Shop::get($inviteLog->shop_id);
            //计算门店分成
            $commission = bcmul($inviteLog->price, bcdiv($shop->commission_ratio, 100, 4), 2);

            //创建待确认邀约
            $invite = Invite::create(array_merge($inviteLog->toArray(), [
                "commission" => $commission
            ]), true);

            //更新支付绑定biz
            $payment->service_biz_id = $invite->id;
            $payment->save();

            //邀约成功后自动关注
            $isFollow = (new UserFollow)->isExist($inviteLog->user_id, $inviteLog->invite_user_id);
            if($isFollow !== Dict::IS_TRUE) {
                UserFollow::create([
                    "user_id" => $inviteLog->user_id,
                    "follow_user_id" => $inviteLog->invite_user_id
                ]);
            }

            //发送模板消息
            try {
                $inviteUser = model('app\common\model\User')->get($inviteLog->invite_user_id);
                $user = model('app\common\model\User')->get($inviteLog->user_id);
                $package = $inviteLog->package;
                if($inviteUser['email']){
                    $subject = "邀约通知";
                    $body = "尊敬的#".$inviteUser['nickname']."，<br>您收到了".$user['nickname']."发起的见面邀约，<br>请登录DMeet直面 微信小程序查看。".Dict::EMAIL_TEXT;
                    (new \app\common\library\NewEmail)->send($inviteUser['email'],$subject,$body);
                 // 邮件同时发送给自己，by Richard  
                    (new \app\common\library\NewEmail)->send('richard@dmeetclub.com', $subject, $body);                       
                }
                // DMSendWechatTempMessage(
                //     $inviteUser->unionid,
                //     Dict::getWechatMessageId(Dict::SEND_WECHAT_MESSAGE_TYPE_INVITE),
                //     Dict::getWechatMiniUrl(Dict::SEND_WECHAT_MESSAGE_TYPE_INVITE),
                //     [
                //         "time5" => ['value' => date("Y-m-d H:i", $inviteLog->meet_time)],
                //         "thing9" => ['value' => $user->name ?: '-'],
                //         "thing15" => ['value' => $inviteLog->address ?: '-'],
                //         "thing16" => ['value' => $package['name'] ?? "-"],
                //     ]
                // );
            } catch (\Exception $ex) {}

            //删除邀约未付款记录
            $inviteLog->delete();

        } catch (\Exception $ex) {
            return false;
        }

        try {
            //发布站内信
            MessageInvitation::create([
                "user_id" => $invite->invite_user_id,
                "from_user_id" => $invite->user_id,
                "biz_type" => 1
            ], true);
        } catch (\Exception $ex) {

        }

        return true;
    }

    /**
     * 同意邀约（待确认）
     * @param Payment|null $payment
     * @return bool
     */
    public function createApproveInvitation(Payment $payment = null)
    {
        try {
            //更新支付状态
            $payment->save(['is_use' => 1, 'notify_time' => time()]);

            //邀约状态变更为待见面
            model('app\common\model\Invite')->allowField(true)->save(
                array_merge((
                    $payment->pay_type == Dict::PAY_TYPE_BALANCE
                    ? ["deposit_pay_type" => $payment->pay_type] : []
                ), [
                    "status" => Dict::INVITE_STATUS_WAIT_MEET,
                ]), [
                    "id" => $payment->service_biz_id
                ]
            );
        } catch (\Exception $ex) {
            return false;
        }
        $invite = model('app\common\model\Invite')->where('id', $payment->service_biz_id)->find();
        //发布站内信
        try {
            if($invite) {
                MessageInvitation::create([
                    "user_id" => $invite->user_id,
                    "from_user_id" => $invite->invite_user_id,
                    "biz_type" => 2
                ], true);
            }
        } catch (\Exception $ex) {}

        //发送模板消息
        try {
            if($invite) {
                $inviteUser = model('app\common\model\User')->get($invite->invite_user_id);
                $user = model('app\common\model\User')->get($invite->user_id);
                $shop = model('app\common\model\Shop')->get($invite->shop_id);
                $package = $invite->package;
                if($user['email']){
                    $subject = "同意邀约通知";
                    $body = "尊敬的#".$user['nickname']."<br>，您发起的见面邀约，收到了".$inviteUser['nickname']."的回复，<br>请登录DMeet直面 微信小程序查看。".Dict::EMAIL_TEXT;
                    (new \app\common\library\NewEmail)->send($user['email'],$subject,$body);
                    // 邮件同时发送给自己，by Richard  
                    (new \app\common\library\NewEmail)->send('richard@dmeetclub.com', $subject, $body);                 
            }
                // DMSendWechatTempMessage(
                //     $user->unionid,
                //     Dict::getWechatMessageId(Dict::SEND_WECHAT_MESSAGE_TYPE_INVITATION_APPROVE),
                //     Dict::getWechatMiniUrl(Dict::SEND_WECHAT_MESSAGE_TYPE_INVITATION_APPROVE),
                //     [
                //         "time10" => ['value' => date("Y-m-d", $invite->meet_time)],
                //         "time4" => ['value' => date("H:i", $invite->meet_time)],
                //         "thing2" => ['value' => $shop->name ?: "-"],
                //         "thing18" => ['value' => $package['name'] ?? "-"],
                //         "thing13" => ['value' => $inviteUser->name?: "-"],
                //     ]
                // );
            }

        } catch (\Exception $ex) {}
        return true;
    }

    /**
     * 发起召集
     * @param Payment|null $payment
     * @return bool
     */
    public function createInviteCall(Payment $payment = null)
    {
        try {
            //更新支付状态
            $payment->save(['is_use' => 1, 'notify_time' => time()]);

            //更新召集状态
            $inviteCall = InviteCall::get($payment->service_biz_id);
            $inviteCall->status = Dict::INVITE_CALL_STATUS_PROCESS;
            $inviteCall->save();

            //生成邀约推广记录
            InvitePromotion::create([
                "user_id" => $inviteCall->user_id,
                "price"   => $inviteCall->publish_fee,
                "pay_type"   => $payment->pay_type,
            ], true);
        } catch (\Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * 购买会员
     * @param Payment $payment
     * @return bool
     */
    public function createMember(Payment $payment)
    {
        try {
            //更新支付状态
            $payment->save(['is_use' => 1, 'notify_time' => time()]);

            //购买的会员类型
            $member = Member::get($payment->service_biz_id);

            //生成会员购买记录
            UserMember::create([
                "user_id"     => $payment->user_id,
                "member_id"   => $member->id,
                "expire"      => $member->expire,
                "price"       => $payment->price,
                "pay_type"    => $payment->pay_type,
            ], true);

            //更新用户状态
            $user = User::get($payment->user_id);
            $user->is_member = Dict::IS_TRUE;
            $user->member_expire = strtotime("+ {$member->expire} months", ($user->member_expire < time() ? time() : $user->member_expire));
            $user->save();
        } catch (\Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     * 生成付款记录
     * @param User $user
     * @param null $serviceType
     * @param null $payType
     * @param null $price
     * @param null $biz
     * @return Payment
     */
    public function createPayment(User $user,$serviceType = null, $payType = null, $price = null, $biz = null)
    {
        //生成支付记录
        $payment = Payment::create([
            "order_no" => $this->getOrderNo(),
            "price" => $price,
            "notify_time" => time(),
            "user_id" => $user->id,
            "pay_type" => $payType,
            "service_type" => $serviceType,
            "service_biz_id" => $biz->id ?? null,
        ], true);

        return $payment;
    }




    /**
     * 支付给商家
     * @param null $userId
     * @param null $price
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function payShop($userId = null, $price=null)
    {
        $model = new \app\common\model\Shop();
        $shop = $model->where('apply_user_id', $userId)->find();
        if(!empty($shop)) {
            $alipay = new Alipay();
            $alipay->transfer($shop, $price);
        }
    }

    /**
     * 生成唯一订单号
     * @return string
     */
    protected function getOrderNo()
    {
        return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }
}