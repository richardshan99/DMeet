<?php

namespace app\common\library;

use app\common\library\pay\Wechat;
use app\common\model\Payment;
use app\common\model\ShopBalance;
use app\common\model\User;
use app\common\model\UserBalance;
use app\common\model\UserRedEnvelopeBalance;
use app\common\model\Virt;
use app\common\model\Blog;
use think\Db;
use think\Queue;
use think\Config;
use DateTime;
use think\Log;

/**
 * 邀约服务
 * Class Menu
 * @package app\common\library
 */
class Invite
{
    /** @var string 邀约人 */
    const ROLE_INVITER = 'role_inviter';
    /** @var string 被邀约人 */
    const ROLE_INVITEE = 'role_invitee';

    protected $user;
    protected $invite;
    protected $role;

    /**
     * Invite constructor.
     * @param User $user
     * @param \app\common\model\Invite $invite
     * @throws \Exception
     */
    public function __construct(User $user, \app\common\model\Invite $invite)
    {
        $this->user = $user;
        $this->invite = $invite;

        if($invite->user_id == $this->user->id) { //邀请人
            $this->role = self::ROLE_INVITER;
        }

        if($invite->invite_user_id == $this->user->id) {//被邀请人
            $this->role = self::ROLE_INVITEE;
        }

        if(empty($this->role)) {
            throw new \Exception("access deny");
        }
    }

    /**
     * 撤销
     * -- 费用全部退还
     * 我邀约的 & 待确认
     */
    public function revoke()
    {
        if($this->role != self::ROLE_INVITER || $this->invite->status != Dict::INVITE_STATUS_WAIT_CONFIRM) {
            throw new \Exception("该邀约无法撤销");
        }

        //调出缴费记录
        $payment = model('app\common\model\Payment')
            ->where([
                "user_id" => $this->user->id,
                "service_type" => Dict::SERVICE_TYPE_INVITATION,
                "service_biz_id" => $this->invite->id,
                "is_use"   => Dict::IS_TRUE
            ])->find();

        if(!$payment) {
            throw new \Exception("操作失败");
        }

        Db::startTrans();
        try {
            //取消邀约
            $this->invite->allowField(true)->save([
                'status' => Dict::INVITE_STATUS_CANCEL,
                'cancel_time' => time(),
                'inviter_refund' => $this->invite->inviter_paid
            ]);

            //邀请人全额退款
            $result = $this->refund($payment, $this->invite->inviter_paid);
            Db::commit();
        } catch (\Exception $ex) {
            Db::rollback();
            throw new \Exception("操作失败");
        }

        return $result;
    }

    /**
     * 取消邀约
     * 待见面 - 我邀约 & 邀约我
     */
    public function cancel()
    {
        if($this->invite->status != Dict::INVITE_STATUS_WAIT_MEET) {
            throw new \Exception("该邀约无法取消");
        }

        if($this->invite->shop_type == Dict::SHOP_TYPE_RESTAURANT){
            /**
             *  x >= 24H ,全退
             *  2H <= x < 24H, 取消方扣 套餐费 *50% *50%， 被取消方 全退
             *  x < 2H, 取消方退 套餐费 *50%， 被取消方 全退
             */
            //取消方支付费用
            $_refund = $this->role == self::ROLE_INVITER ? $this->invite->inviter_paid : $this->invite->invitee_paid;
            //剩余时间  单位：H
            $remainTime = bcdiv(bcsub($this->invite->meet_time, time()), 3600, 2);

            if($remainTime >= 24) { //x >= 24H ,全退
                $refund = $_refund;
            } elseif ($remainTime >= 2){ // 2H <= x < 24H, 取消方扣 套餐费 *50% *50%， 被取消方 全退
                $refund = bcsub($_refund, bcmul(bcmul($this->invite->price, 0.5, 2), 0.5, 2), 2);
            }else { // x < 2H, 取消方退 套餐费 *50%， 被取消方 全退
                $refund = bcsub($_refund, bcmul($this->invite->price, 0.5, 2), 2);
            }
        }else{
            /**
             *  当天取消不退保证金  前一天取消退保证金20% 前两天可免费取消 以每日的24:00作为划分
             */
            // 获取当前时间戳
           // 获取当前时间戳
            $currentTime = time();
            if($currentTime > $this->invite->meet_time){
                throw new \Exception("该邀约无法取消");
            }
            // 获取当前日期和见面日期
            $current_date = date('Y-m-d', $currentTime);
            $meet_date = date('Y-m-d', $this->invite->meet_time);

            // 将日期字符串转换为DateTime对象
            $current_date_obj = new DateTime($current_date);
            $meet_date_obj = new DateTime($meet_date);

            // 计算天数差
            $date_difference = $current_date_obj->diff($meet_date_obj)->days;
            
            //取消方支付费用
            $_refund = $this->role == self::ROLE_INVITER ? $this->invite->inviter_paid : $this->invite->invitee_paid;
            if ($date_difference >= 2) { // 前两天可免费取消
                $refund = $_refund;
            } elseif ($date_difference >= 1) { // 前一天取消退保证金20%
                // 先计算应退还的金额，即支付金额的20%
                $refund = bcmul($_refund, 0.2, 2);
            } else { // 当天取消不退保证金
                $refund = 0;
            }
        }


        //邀请人支付记录
        $inviterPayment = model('app\common\model\Payment')
            ->where('is_use', Dict::IS_TRUE)
            ->where('service_type', Dict::SERVICE_TYPE_INVITATION)
            ->where('service_biz_id', $this->invite->id)
            ->find();

        //被邀请人支付记录
        $inviteePayment = model('app\common\model\Payment')
            ->where('is_use', Dict::IS_TRUE)
            ->where('service_type', Dict::SERVICE_TYPE_APPROVE_INVITATION)
            ->where('service_biz_id', $this->invite->id)
            ->find();

        Db::startTrans();
        $compensationBalance = 0;
        try {
            //取消邀约
            $this->invite->allowField(true)->save(['status' => Dict::INVITE_STATUS_CANCEL, 'cancel_time' => time()]);
            //邀请人退款
            $this->refund($inviterPayment, $this->role == self::ROLE_INVITER ? $refund : $this->invite->inviter_paid);
            //被邀请人退款
            $this->refund($inviteePayment, $this->role == self::ROLE_INVITEE ? $refund : $this->invite->invitee_paid);

            if($this->invite->shop_type == Dict::SHOP_TYPE_RESTAURANT){ // 只有餐厅类型取消有 补偿被取消人20元余额
                $compensationBalance = 20;
                //补偿被取消人20元余额
                $user = User::get($this->role == self::ROLE_INVITER ? $this->invite->invite_user_id : $this->invite->user_id);
                (new UserBalance)->generate($user, Dict::USER_BALANCE_TYPE_INVITE_INCR, $compensationBalance);
            }else{
                if($refund <= 0){
                    $user = User::get($this->role == self::ROLE_INVITER ? $this->invite->invite_user_id : $this->invite->user_id);
                    //补偿被取消人保证金的60%  非餐厅的被邀约人的支付金额就是保证金 所以下面是使用被邀约人的支付金额
                    $returnMoney =  sprintf("%.2f",$this->invite->invitee_paid*0.6);
                    if($returnMoney > 0){
                       $wechat = new Wechat($user);
                       $wechat->transfer($returnMoney);  
                    }
                    Log::init(['type'  =>  'File', 'path'  =>  ROOT_PATH.'logs/cancel_invite/']);
                    Log::write('用户ID：'.$this->role == self::ROLE_INVITER ? $this->invite->invite_user_id : $this->invite->user_id.'金额：'.$returnMoney.'邀约ID：'.$this->invite->id);
                }
               
            }

            Db::commit();
        } catch (\Exception $ex) {
            Db::rollback();
            throw new \Exception("操作失败");
        }

        try {
            //给被取消方发送站内信
            $library = new Message($user);
            $payment = $this->role == self::ROLE_INVITER ? $inviteePayment : $inviterPayment;
            $library->cancelInvitation($payment, $compensationBalance);
        }catch (\Exception $ex) {}
        return true;
    }

    /**
     * 同意
     * 邀约我 & 待确认
     * @param null $payType
     * @return array
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Exception
     */
    public function approve($payType = null)
    {
        if($this->role != self::ROLE_INVITEE || $this->invite->status != Dict::INVITE_STATUS_WAIT_CONFIRM) {
            throw new \Exception("该邀约无法操作");
        }

        if($this->invite->pay_mode != Dict::INVITE_PAY_MODE_MY && $payType == Dict::PAY_TYPE_BALANCE) {
            throw new \Exception("邀约费用无法使用余额支付");
        }

        try {
            //发起支付
            $payment = new \app\common\library\pay\Payment($this->user, $payType);
            $result = $payment->approveInvitation($this->invite);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
        return $result;
    }

    /**
     * 拒绝
     * 邀约我 & 待确认
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function reject()
    {
        if($this->role != self::ROLE_INVITEE || $this->invite->status != Dict::INVITE_STATUS_WAIT_CONFIRM) {
            throw new \Exception("该邀约无法操作1");
        }

        //调出邀请人缴费记录
        $payment = model('app\common\model\Payment')
            ->where([
                "user_id" => $this->invite->user_id,
                "service_type" => Dict::SERVICE_TYPE_INVITATION,
                "service_biz_id" => $this->invite->id,
                "is_use"   => Dict::IS_TRUE
            ])->find();

        if(!$payment) {
            throw new \Exception("该邀约无法操作2");
        }

        Db::startTrans();
        try {
            //取消邀约
            $this->invite->allowField(true)->save([
                'status' => Dict::INVITE_STATUS_CANCEL,
                'cancel_time' => time(),
                'inviter_refund' => $this->invite->inviter_paid
            ]);

            //邀请人全额退款
            $result = $this->refund($payment, $this->invite->inviter_paid);
            Db::commit();
        } catch (\Exception $ex) {
            Db::rollback();
            throw new \Exception("该邀约无法操作3");
        }

        if($result !== false) {
            // 发送站内信
            try {
                $library = new \app\common\library\Message(\app\common\model\User::get($this->invite->user_id));
                $library->refuseInvitation($payment);
            }catch (\Exception $ex) {}

            //发送模板消息
            // try {
            //     $inviteUser = model('app\common\model\User')->get($this->invite->invite_user_id);
            //     $user = model('app\common\model\User')->get($this->invite->user_id);
            //     DMSendWechatTempMessage(
            //         $user->unionid,
            //         Dict::getWechatMessageId(Dict::SEND_WECHAT_MESSAGE_TYPE_INVITATION_REFUSE),
            //         Dict::getWechatMiniUrl(Dict::SEND_WECHAT_MESSAGE_TYPE_INVITATION_REFUSE),
            //         [
            //             "time7" => ['value' => date("Y-m-d", $this->invite->meet_time)],
            //             "time8" => ['value' => date("H:i", $this->invite->meet_time)],
            //             "thing15" => ['value' => $this->invite->address?: "-"],
            //             "thing10" => ['value' => $inviteUser->name?: "-"],
            //             "time5" => ['value' => date("Y-m-d H:i")],
            //         ]
            //     );
            // } catch (\Exception $ex) {}
        }

        return $result;
    }

    /**
     * 见面核销
     */
    public function meetingVerify()
    {
        $extendData = [];
        $shop = model('app\common\model\Shop')->where('id', $this->invite->shop_id)->find();
        Db::startTrans();
        try {
            $payMode = $this->invite->pay_mode;
            //邀请人付餐费，被邀请人付履约金- 退费
            if($payMode == Dict::INVITE_PAY_MODE_MY && $this->role == self::ROLE_INVITEE) {//被邀请
                $payment = model('app\common\model\Payment')
                    ->where([
                        "user_id" => $this->invite->invite_user_id,
                        "service_type" => Dict::SERVICE_TYPE_APPROVE_INVITATION,
                        "service_biz_id" => $this->invite->id,
                        "is_use"   => Dict::IS_TRUE
                    ])->find();
                if($payment) {
                    //被邀请人退履约金
                    $this->refund($payment, $this->invite->invitee_paid);
                    $extendData['invitee_refund'] = $this->invite->invitee_paid;
                }

            }

            //邀请人付履约金- 退费，被邀请人付餐费
            if($payMode == Dict::INVITE_PAY_MODE_YOU && $this->role == self::ROLE_INVITEE) {//邀请人
                $payment = model('app\common\model\Payment')
                    ->where([
                        "user_id" => $this->invite->user_id,
                        "service_type" => Dict::SERVICE_TYPE_INVITATION,
                        "service_biz_id" => $this->invite->id,
                        "is_use"   => Dict::IS_TRUE
                    ])->find();
                if($payment) {
                    //邀请人退履约金
                    $this->refund($payment, $this->invite->inviter_paid);
                    $extendData['inviter_refund'] = $this->invite->inviter_paid;
                }
            }


            if($this->role == self::ROLE_INVITER) { //邀请人
                $_inviteData = [
                    'inviter_is_verify' => Dict::IS_TRUE,
                    'inviter_verify_time' => time(),
                ];

                //被邀请人若核销，邀约状态变为已完成
                if($this->invite->invitee_is_verify == Dict::IS_TRUE) {
                    $_inviteData['status'] = Dict::INVITE_STATUS_FINISH;
                    //门店收入
                    (new ShopBalance)->generate($shop, Dict::SHOP_BALANCE_TYPE_REVENUE_INCR, $this->invite->commission);
                    $inviterName = model('app\common\model\User')->where('id', $this->invite->user_id)->value('nickname');
                    $inviteeName = model('app\common\model\User')->where('id', $this->invite->invite_user_id)->value('nickname');
                    //添加见面公示栏
                    Virt::create([
                        "content" => "{$inviterName}和{$inviteeName}在{$shop['name']}见面啦",
                        "source"  => Dict::MEET_DATA_SOURCE_AUTO
                    ]);
                }
            }

            if($this->role == self::ROLE_INVITEE) { //被邀请人
                $_inviteData = [
                    'invitee_is_verify' => Dict::IS_TRUE,
                    'invitee_verify_time' => time(),
                ];

                //邀请人若核销，邀约状态变为已完成
                if($this->invite->inviter_is_verify == Dict::IS_TRUE) {
                    $_inviteData['status'] = Dict::INVITE_STATUS_FINISH;

                    //门店收入
                    (new ShopBalance)->generate($shop, Dict::SHOP_BALANCE_TYPE_REVENUE_INCR, $this->invite->commission);
                    $inviterName = model('app\common\model\User')->where('id', $this->invite->user_id)->value('nickname');
                    $inviteeName = model('app\common\model\User')->where('id', $this->invite->invite_user_id)->value('nickname');
                    //添加见面公示栏
                    Virt::create([
                        "content" => "{$inviterName}和{$inviteeName}在{$shop['name']}见面啦",
                        "source"  => Dict::MEET_DATA_SOURCE_AUTO
                    ]);
                }
            }

            $this->invite->allowField(true)->save(array_merge($_inviteData, $extendData));


            Db::commit();
        } catch (\Exception $ex) {
            Db::rollback();
            throw new \Exception("核销失败");
        }

        //核销成功,30分钟内，对方需核销，否则自动取消
        Queue::later(30*60, 'app\job\CancelOrder', ['id' => $this->invite->id, 'status'=>$this->invite->status], 'order');
        return true;
    }

     /**
     * 超时未核销
     * 待见面 - 核销
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cancelOrder()
    {
        if($this->invite->status != Dict::INVITE_STATUS_WAIT_MEET) {
            throw new \Exception("您无法操作该邀约");
        }

        //调出待退费用户缴费记录
        $payment = model('app\common\model\Payment')
            ->where([
                "user_id"        => $this->user->id,
                "service_type"   => ['in', [Dict::SERVICE_TYPE_INVITATION, Dict::SERVICE_TYPE_APPROVE_INVITATION]],
                "service_biz_id" => $this->invite->id,
                "is_use"         => Dict::IS_TRUE,
                "is_refund"      => Dict::IS_FALSE
            ])->find();
        if(!$payment) {
            throw new \Exception("操作失败1");
        }

        Db::startTrans();
        try {
            //取消邀约
            $extend = [];
            $refundPrice = 0;
            if($this->role == self::ROLE_INVITER) {
                $refundPrice = $this->invite->inviter_paid;
                $extend['inviter_refund'] = $refundPrice;
            }
            if($this->role == self::ROLE_INVITEE) {
                $extend['invitee_refund'] = $this->invite->invitee_paid;
                $refundPrice = $this->invite->invitee_paid;
            }
            $this->invite->allowField(true)->save(array_merge([
                'status' => Dict::INVITE_STATUS_CANCEL,
                'cancel_time' => time(),
            ], $extend));

            //全额退款
            $result = $this->refund($payment, $refundPrice);

            if($this->invite->shop_type == Dict::SHOP_TYPE_NO_RESTAURANT){ // 非餐厅
                $user = User::get($this->user->id);
                //补偿被取消人保证金的60%  非餐厅的被邀约人的支付金额就是保证金 所以下面是使用被邀约人的支付金额
                $wechat = new Wechat($user);
                $returnMoney =  sprintf("%.2f",$this->invite->invitee_paid*0.6);
                if($returnMoney > 0){
                   $wechat->transfer($returnMoney);  
                }
                $returnBalanceMoney = 0;
            }else{
                //补偿被取消人20元余额
                $user = User::get($this->user->id);
                $returnBalanceMoney = 20;
                (new UserBalance)->generate($user, Dict::USER_BALANCE_TYPE_INVITE_INCR, $returnBalanceMoney);
                //补偿被取消人20元现金
                $wechat = new Wechat($user);
                $returnMoney = 20;
                $wechat->transfer($returnMoney);
            }
            Log::init(['type'  =>  'File', 'path'  =>  ROOT_PATH.'logs/cancel_invite/']);
            Log::write('用户ID：'.$this->user->id.'金额：'.$returnMoney.'邀约ID：'.$this->invite->id);
            Db::commit();
        } catch (\Exception $ex) {
            Db::rollback();
            throw new \Exception($ex->getMessage());
        }

        if($result !== false) {
            try {
                $library = new \app\common\library\Message($user);
                $library->cancelInvitation($payment, $returnBalanceMoney, $returnMoney);
            }catch (\Exception $ex) {}
        }

        return $result;
    }
    
     /**
     * 签到
     * @param $data
     * @return true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function signIn($data,$timezone)
    {
        Db::startTrans();
        $extendData = $_inviteData = [];
        // 用户当前定位、首页增加距离显示
        $position = $data['position'];
      
        if(!$position){
            throw new \Exception("请先获取定位");
        }
        $lon = $lat = '';
        if(isset($position) && false !== strpos($position, ',')) {
            list($lon, $lat) = explode(',', $position);
        }
       
        if(!$lon || !$lat){
            throw new \Exception("请先获取定位");
        }
        $shop = model('app\common\model\Shop')->field("*,(st_distance_sphere(point(".$lon.",".$lat."), `point`)/1000) as distance, ST_AsText(`point`) as point_text")->where('id', $this->invite->shop_id)->find();
        try {
            $time = time();
            $deposit = Config::get('site.sign_in_range');
            // 验证距离
            if($shop->distance*1000 >= $deposit){
                throw new \Exception("未在签到范围内");
            }
           
            //见面时间前半小时或后半小时之外
            if($time - $this->invite->meet_time > 30*60 || $this->invite->meet_time - $time > 30*60){
                throw new \Exception("未到见面时间");
            }
           
          
            if(empty($data['sign_image'])){
                throw new \Exception("请上传签到图片");
            }

            $inviteeUser = User::get($this->invite->invite_user_id);
            if($this->role == self::ROLE_INVITER) { //邀请人
                $_inviteData = [
                    'inviter_is_verify' => Dict::IS_TRUE,
                    'inviter_verify_time' => time(),
                    'inviter_is_share' => $data['is_share'] ?:  Dict::IS_TRUE,
                    'inviter_sign_image' => $data['sign_image'],
                    'inviter_sign_longitude_and_latitude' => $position
                ];
                
                $message = [
                    'content' => '对方已签到，点击查看位置',
                    'longitude_and_latitude' => $position
                ];
                (new \app\common\model\Chat)->send($this->invite->user_id, $this->invite->invite_user_id, $message);

                //被邀请人若签到，邀约状态变为已完成
                if($this->invite->invitee_is_verify == Dict::IS_TRUE) {
                    if($this->invite->meeting_red_envelope_price > 0){
                        //被邀请人获得见面红包
                        (new UserRedEnvelopeBalance)->generate($inviteeUser, Dict::USER_RED_BALANCE_1, $this->invite->meeting_red_envelope_price);
                    }
                    (new Blog)->synchronousShare($this->invite,$this->invite->user_id,$inviteeUser,$data['is_share'] ?:  Dict::IS_TRUE,$this->invite->invitee_is_share);
                    $this->signRefund($this->invite,$this->invite->invitee_paid);
                    $_inviteData['status'] = Dict::INVITE_STATUS_FINISH;
                    $inviterName = model('app\common\model\User')->where('id', $this->invite->user_id)->value('nickname');
                    $inviteeName = model('app\common\model\User')->where('id', $this->invite->invite_user_id)->value('nickname');
                    //添加见面公示栏
                    Virt::create([
                        "content" => "{$inviterName}和{$inviteeName}在{$shop['name']}见面啦",
                        "source"  => Dict::MEET_DATA_SOURCE_AUTO
                    ]);
                }
            }

            if($this->role == self::ROLE_INVITEE) { //被邀请人

                $_inviteData = [
                    'invitee_is_verify' => Dict::IS_TRUE,
                    'invitee_verify_time' => time(),
                    'invitee_is_share' => $data['is_share'] ?:  Dict::IS_TRUE,
                    'invitee_sign_image' => $data['sign_image'],
                    'invitee_sign_longitude_and_latitude' => $position
                ];
                
                  $message = [
                    'content' => '对方已签到，点击查看位置',
                    'longitude_and_latitude' => $position
                ];
                (new \app\common\model\Chat)->send($this->invite->invite_user_id, $this->invite->user_id, $message);

                //邀请人若签到，邀约状态变为已完成
                if($this->invite->inviter_is_verify == Dict::IS_TRUE) {
                    if($this->invite->meeting_red_envelope_price > 0){
                        //被邀请人获得见面红包
                        (new UserRedEnvelopeBalance)->generate($inviteeUser, Dict::USER_RED_BALANCE_1, $this->invite->meeting_red_envelope_price);
                    }
                    (new Blog)->synchronousShare($this->invite,$this->invite->user_id,$inviteeUser,$this->invite->inviter_is_share,$data['is_share'] ?:  Dict::IS_TRUE);
                    $this->signRefund($this->invite,$this->invite->invitee_paid);
                    $_inviteData['status'] = Dict::INVITE_STATUS_FINISH;
                    $inviterName = model('app\common\model\User')->where('id', $this->invite->user_id)->value('nickname');
                    $inviteeName = model('app\common\model\User')->where('id', $this->invite->invite_user_id)->value('nickname');
                    //添加见面公示栏
                    Virt::create([
                        "content" => "{$inviterName}和{$inviteeName}在{$shop['name']}见面啦",
                        "source"  => Dict::MEET_DATA_SOURCE_AUTO
                    ]);
                }
            }

            $this->invite->allowField(true)->save(array_merge($_inviteData, $extendData));


            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new \Exception($e->getMessage());
        }

        //签到成功,30分钟内，对方需核销，否则自动取消
        Queue::later(30*60, 'app\job\CancelOrder', ['id' => $this->invite->id, 'status'=>$this->invite->status], 'order');
        return true;
    }

    /**
     * 退款
     * @param Payment $payment 支付记录
     * @param $price 退款金额
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    protected function refund(Payment $payment, $price)
    {
        if($payment->is_refund != Dict::IS_FALSE) {
            throw new \Exception('已退款');
        }

        if(!$price) return true;

        try {
            $payment->allowField(true)->save([
                'is_refund'    => Dict::IS_TRUE,
                'refund_price' => $price,
                'refund_time'  => time(),
            ]);

            if($payment->pay_type == Dict::PAY_TYPE_WECHAT) { //微信退款
                $wechat = new Wechat(User::get($payment->user_id));
                $wechat->refundActivity($payment, $price);
            }

            if($payment->pay_type == Dict::PAY_TYPE_BALANCE) { //余额支付
                //新增积分记录
                (new UserBalance)->generate(User::get($payment->user_id), Dict::USER_BALANCE_TYPE_INVITE_INCR, $price);
            }
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }
    
  
    /**
     * 签到退款
     * @param Payment $payment 支付记录
     * @param $price 退款金额
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    protected function signRefund($invite,$price)
    {

        try {
            $inviter_payment = model('app\common\model\Payment')
                ->where([
                    "user_id" => $invite->user_id,
                    "service_type" => Dict::SERVICE_TYPE_INVITATION,
                    "service_biz_id" => $invite->id,
                    "is_use"   => Dict::IS_TRUE
                ])->find();
            if($inviter_payment) {
                //被邀请人退履约金
                $inviter_status = $this->refund($inviter_payment, $price);
                if(!$inviter_status){
                    throw new \Exception('退款失败');
                }
            }


            $invitee_payment = model('app\common\model\Payment')
                ->where([
                    "user_id" => $invite->invite_user_id,
                    "service_type" => Dict::SERVICE_TYPE_APPROVE_INVITATION,
                    "service_biz_id" => $invite->id,
                    "is_use"   => Dict::IS_TRUE
                ])->find();
            if($invitee_payment) {
                //被邀请人退履约金
                $invitee_status = $this->refund($invitee_payment, $price);
                if(!$invitee_status){
                    throw new \Exception('退款失败');
                }
            }

        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }

}
