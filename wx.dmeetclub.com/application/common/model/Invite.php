<?php

namespace app\common\model;

use app\common\library\Dict;
use app\common\library\pay\Payment;
use DI\CompiledContainer;
use fast\Tree;
use think\Db;
use think\Model;

/**
 * 邀约表
 */
class Invite Extends Model
{

    protected $name = 'invite';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;

    protected $insert = [
        "status" => Dict::INVITE_STATUS_WAIT_CONFIRM,
        "inviter_is_verify" => Dict::IS_FALSE,
        "invitee_is_verify" => Dict::IS_FALSE,
    ];

    protected $type = [
        "package" => 'json'
    ];

    /**
     * 状态 格式化
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getStatusTextAttr($value, $row)
    {
        return Dict::getInviteStatus($row['status']);
    }

    /**
     * 见面时间 格式化
     * @param $value
     * @param $row
     * @return false|string
     */
    public function getMeetTimeTextAttr($value, $row)
    {
        return $row['meet_time'] ? date('Y-m-d H:i', $row['meet_time']) : "";
    }

    /**
     * 付款方式 格式化
     * @param $value
     * @param $row
     * @return mixed|string
     */
    public function getPayModeTextAttr($value, $row)
    {
        return DICT::getInvitePayMode($row['pay_mode']);
    }

    /**
     * 邀约类型
     * @param $user
     * @return int|null
     */
    public function inviteType($user)
    {
        return $user->id == $this->user_id ? Dict::INVITE_TYPE_MY_INVITE : (
                $user->id == $this->invite_user_id ? Dict::INVITE_TYPE_INVITE_ME : null
            );
    }

    /**
     * 付款方式
     * @param $user
     * @return int|mixed|null
     */
    public function calPayType($user)
    {
        $payMode = $this->pay_mode;
        if($user->id == $this->invite_user_id) { //被邀约人
            if($this->pay_mode == Dict::INVITE_PAY_MODE_MY) { //如果邀请人设置"我付"，此处转为你付
                $payMode = Dict::INVITE_PAY_MODE_YOU;
            }

            if($this->pay_mode == Dict::INVITE_PAY_MODE_YOU) {//如果邀请人设置"你付"，此处转为我付
                $payMode = Dict::INVITE_PAY_MODE_MY;
            }
        }

        return $payMode;
    }

    /**
     * 计算金额
     * @param $user
     * @return int|mixed
     */
    public function calPrice($user)
    {
        $price = 0;
        if($user->id == $this->user_id) { //我是邀约人
            $price = $this->inviter_paid;
        }

        if($user->id == $this->invite_user_id) { //我是被邀约人
            $price = $this->invitee_paid;
        }

        return $price;
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo("app\common\model\User", 'user_id')->setEagerlyType(0);
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function inviteuser()
    {
        return $this->belongsTo("app\common\model\User", 'invite_user_id')->setEagerlyType(0);
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function shop()
    {
        return $this->belongsTo("app\common\model\Shop", 'shop_id')->setEagerlyType(0);
    }
    
     /**
     * @return \think\model\relation\hasOne
     */
    public function change()
    {
        return $this->hasOne("app\common\model\InviteChange", 'invite_id','','','left')->setEagerlyType(0);
    }


    /**
     * 邀约列表
     * @param User $user
     * @param array $where
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function list(User $user, $where = [],$timezong,$whereRaw = null)
    {
         $list = $this->with(['user', 'inviteuser', 'shop', 'change'])
            ->whereRaw('invite.user_id = '.$user->id. ' or invite_user_id = '.$user->id);
        if($whereRaw){
           $list = $list->whereRaw($whereRaw)
            ->where($where)
            ->order([
                'status'      => 'asc',
                'create_time' => 'desc'
            ])
            ->select();  
        }else{
            $list = $list
            ->where($where)
            ->order([
                'status'      => 'asc',
                'create_time' => 'desc'
            ])
            ->select();  
        }
        
        $areaList = model('app\common\model\AreaNew')->column('id,name');
        // var_dump($timezone);die;
        foreach($list as $key => $item)
        {

            $item->user->birth_year = date('y', strtotime($item->user->birth));
            $item->user->area = DMUserArea($areaList, $item->user->area_path);
            $item->inviteuser->birth_year = date('y', strtotime($item->inviteuser->birth));
            $item->inviteuser->area = DMUserArea($areaList, $item->inviteuser->area_path);


            $item->invite_type = $item->inviteType($user);
            $item->pay_mode = $item->calPayType($user);
            $item->price = $item->calPrice($user);

            $item->change->shop = (new Shop)->field('id,name')->where(['id'=> $item->change->shop_id])->find();
            $item->visible([
                'id', 'invite_type', 'status', 'address', 'pay_mode', 'price', 'package','shop_type','meeting_red_envelope_price','inviter_is_verify','invitee_is_verify','inviter_sign_longitude_and_latitude','invitee_sign_longitude_and_latitude','meet_time',//门店类型
                'user' => ['id','nickname','gender','height', 'area','is_member','contact_mobile','mobile'],
                'inviteuser' => ['id','nickname','gender','height', 'area','is_member','contact_mobile','mobile'],
                'shop' => ['id','name'],
                'change' => ['id','meet_time','address','status','shop'],
            ]);

            //门店区域
            $areaPath = $item->shop->area_path ?: "";
            $areaPath = explode(",", trim($areaPath, ','));
            $areaLastId = array_pop($areaPath);
            $item->area_name = $areaList[$areaLastId] ?? "";
            
            $item->meet_time_new_text = timezongTime($timezong,date('Y-m-d H:i',$item->meet_time));
            $item->change->meet_time_new_text = timezongTime($timezong,date('Y-m-d H:i',$item->change->meet_time));
            //邀约人区域
            $item->append([
                'status_text', 'meet_time_text','meet_time_new_text', 'area_name',
                'user' => ['avatar_text', 'birth_year', 'is_cert_education'],
                'inviteuser' => ['avatar_text','birth_year', 'is_cert_education'],
                'change' => ['meet_time_text','status_text','meet_time_new_text']
            ]);
        }

        return $list;
    }


    /**
     * 付款
     * @param User $user
     * @param $params
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pay(User $user, $params = [] ,$timezong)
    {

        //被邀请人
        $inviteUserId = $params['invite_user_id'] ?? null;
        if(empty($inviteUserId)) {
            throw new \Exception("请选择邀约人");
        }
        $inviteUserId = User::get($inviteUserId);
        if(!$inviteUserId) {
            throw new \Exception("邀约人不存在");
        }

        //门店
        $shopId = $params['shop_id'] ?? null;
        if(empty($shopId)) {
            throw new \Exception("请选择门店");
        }
        $shop = Shop::get($shopId);
        if(!$shop) {
            throw new \Exception("门店不存在");
        }
        $this->checkUser($user);
        list($packageId,$package,$meetingRedEnvelopePrice) = $this->checkShop($shop,$params);

        //见面时间
        $meetTime = $params['meet_time'] ?? null;
        if(empty($meetTime)) {
            throw new \Exception("请选择见面时间");
        }
        $meetTime = strtotime($meetTime);
        //每天只允许有一次待见面 & 待确认的邀约
        $isExists = model('app\common\model\Invite')->where([
            'user_id' => $user->id,
            'status'  => ['in', [Dict::INVITE_STATUS_WAIT_MEET, Dict::INVITE_STATUS_WAIT_CONFIRM]],
            'meet_time' => ['between', [strtotime(date("Y-m-d", $meetTime)), strtotime(date("Y-m-d 23:59:59", $meetTime))]]
        ])->find();
        if($isExists) {
            throw new \Exception("每天只允许有一次待确认或待见面邀约");
        }

        //只允许选择第2天 - 第14天 
        // $secondDay   = strtotime('+1 day', strtotime(date("Y-m-d")));
        $secondDay   = strtotime(date("Y-m-d"));
        $fourteenDay = strtotime('+14 days', strtotime(date("Y-m-d")));
        if($meetTime > $fourteenDay || $meetTime < $secondDay) {
            throw new \Exception("见面时间只能选择当天-第14天");
        }

        //当天10点
        $_tmpTen = strtotime(date("Y-m-d 10:00", $meetTime));
        //当天12点
        $_tmpTwelve = strtotime(date("Y-m-d 21:00", $meetTime));
        
        // 10：00 - 21：00
        if($meetTime > $_tmpTwelve || $meetTime < $_tmpTen) {
            throw new \Exception("见面时间为每天10：00-21：00");
        }

        //生成邀约待支付记录
        try {
            $result = (new InviteLog)->generate($user, array_merge($params, [
                'meet_time'  => timezongTimestamp($timezong,$meetTime),
                'package_id' => $packageId,
                'package'    => $package,
                'meeting_red_envelope_price'=>$meetingRedEnvelopePrice,
                'address'    => $shop->address,
                'shop_type'  => $shop->type
            ]));

        }catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
        return $result;
    }

    /**
     * 不同会员-下单数量不同
     * @param $user
     * @return true
     * @throws Exception
     */
    public function checkUser($user): bool
    {
        $currentTime = time();
        if($user->is_member == Dict::IS_TRUE && $user->member_expire >$currentTime){ // 会员是vip
            // 获取本周开始时间戳
            $weekStart = strtotime('this week', $currentTime);
            // 获取本周结束时间戳
            $weekEnd = strtotime('next week', $weekStart) - 1;

            $count = model('app\common\model\Invite')->where([
                'user_id' => $user->id,
                'status' => ['in', [Dict::INVITE_STATUS_WAIT_MEET, Dict::INVITE_STATUS_WAIT_CONFIRM]],
                'create_time' => ['between', [$weekStart, $weekEnd]]
            ])->count();
            if($count >= 5) {
                throw new \Exception("每周只能同时存在5个邀约订单");
            }
        }else{ // 非会员
            // 获取 7 天前的时间戳
            $sevenDaysAgo = $currentTime - (7 * 24 * 60 * 60);
            $count = model('app\common\model\Invite')->where([
                'user_id' => $user->id,
                'status' => ['in', [Dict::INVITE_STATUS_WAIT_MEET, Dict::INVITE_STATUS_WAIT_CONFIRM]],
                'create_time' => ['between', [$sevenDaysAgo, $currentTime]]
            ])->count();
            if($count >= 1) {
                throw new \Exception("非会员一周内只能邀约一次");
            }
        }
        return true;
    }

    /**
     * 不同门店类型验证参数
     */
    public function checkShop($shop,$params)
    {
        $meetingRedEnvelopePrice = 0;
        if($shop['type'] == Dict::SHOP_TYPE_RESTAURANT){  // 餐厅
            //餐费不能用余额支付
            if($params['pay_mode'] != Dict::INVITE_PAY_MODE_YOU && $params['pay_type'] == Dict::PAY_TYPE_BALANCE) {
                throw new \Exception("不能使用余额支付");
            }

            //套餐
            $packageId = $params['package'] ?? null;
            if(empty($packageId)) {
                throw new \Exception("请选择套餐");
            }
            if($packageId == 1) { //套餐1
                $package = $shop['package1'];
            }
            if($packageId == 2) { //套餐2
                $package = $shop['package2'];
            }
            if(!isset($package)) {
                throw new \Exception("套餐不存在");
            }

        }else{  // 非餐厅
            $packageId = null;
            $package = null;
            if($params['pay_mode'] != Dict::INVITE_PAY_MODE_HALF) {// AA
                throw new \Exception("非餐厅只能选择AA支付");
            }
            $meetingRedEnvelopeId = $params['meeting_red_envelope_id']  ?? 0;

            if($meetingRedEnvelopeId){
                $meetingRedEnvelopePrice = (new MeetingRedEnvelope)->where(['id'=>$meetingRedEnvelopeId])->value('price');
                $meetingRedEnvelopePrice = $meetingRedEnvelopePrice ?? 0;
            }

            if($params['pay_type'] == Dict::PAY_TYPE_BALANCE) {
                throw new \Exception("仅支持微信付款");
            }
        }
        return [$packageId,$package,$meetingRedEnvelopePrice];
    }

    /**
     * 从召集发出邀约
     * @param User $user
     * @param $inviteeId
     * @param InviteCall $inviteCall
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function call(User $user, $inviteeId, InviteCall $inviteCall)
    {
        //被邀请人
        if(empty($inviteeId)) {
            throw new \Exception("请选择邀约人");
        }
        $inviteUser = User::get($inviteeId);
        if(!$inviteUser) {
            throw new \Exception("邀约人不存在");
        }

        //每天只允许有一次待见面 & 待确认的邀约
        $isExists = model('app\common\model\Invite')->where([
            'user_id' => $user->id,
            'status'  => ['in', [Dict::INVITE_STATUS_WAIT_MEET, Dict::INVITE_STATUS_WAIT_CONFIRM]],
            'meet_time' => ['between', [strtotime(date("Y-m-d", $inviteCall->meet_time)), strtotime(date("Y-m-d 23:59:59", $inviteCall->meet_time))]]
        ])->find();
        if($isExists) {
            throw new \Exception("每天只允许有一次待确认或待见面邀约");
        }

        //获取门店
        $shop = Shop::get($inviteCall->shop_id);
        //计算门店分成
        $commission = bcmul($inviteCall->price, bcdiv($shop->commission_ratio, 100, 4), 2);

        Db::startTrans();
        try {
            //创建待确认邀约
            $invite = Invite::create(array_merge($inviteCall->toArray(), [
                "id" => null,
                "commission" => $commission,
                "invite_user_id" => $inviteeId
            ]), true);

            //更新支付绑定biz
            $payment = model('app\common\model\Payment')->where([
                'user_id' => $user->id,
                'service_type' => Dict::SERVICE_TYPE_INVITE_CALL,
                'service_biz_id' => $inviteCall->id
            ])->find();
            $payment->service_biz_id = $invite->id;
            $payment->service_type = Dict::SERVICE_TYPE_INVITATION;
            $payment->save();

            //更新召集状态，绑定邀约id
            $inviteCall->invite_id = $invite->id;
            $inviteCall->status = Dict::INVITE_CALL_STATUS_SUCCESS;
            $result = $inviteCall->save();

            Db::commit();
        }catch (\Exception $ex) {
            Db::rollback();
            throw new \Exception($ex->getMessage());
        }
        return $result;
    }

    /**
     * 订单列表
     * @param Shop $shop
     * @param array $data 筛选
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function orderList(Shop $shop, $data=[])
    {
        //扩展搜索
        $andWhere = [];

        //搜索：昵称
        if(isset($data['nickname']) && !empty($data['nickname'])) {
            $andWhere['user.nickname|inviteuser.nickname'] = ['like', '%'.$data['nickname'].'%'];
        }

        //搜索：见面时间段
        if(isset($data['range_meet_time']) && !empty($data['range_meet_time'])) {
            $timeRange = explode(' - ', $data['range_meet_time']);
            $andWhere['meet_time'] = ['between', [strtotime($timeRange[0]), strtotime($timeRange[1]. ' 23:59:59')]];
        }

        //搜索：邀约状态
        if(isset($data['status']) && !empty($data['status'])) {
            $andWhere['invite.status'] = $data['status'];
        }


        $list = $this->with(['user', 'inviteuser'])->where([
            "invite.shop_id" => $shop->id,
            "invite.status"  => ['in', [
                Dict::INVITE_STATUS_WAIT_MEET, Dict::INVITE_STATUS_FINISH, Dict::INVITE_STATUS_CANCEL
            ]],
        ])->where($andWhere)->order('invite.create_time', 'desc')
        ->paginate(20);
//        echo $this->with(['user', 'inviteuser'])->getLastSql();exit;

        foreach($list as $key => $item)
        {
            $item->visible([
                'address', 'pay_mode', 'price', 'package', 'deposit', 'status', 'inviter_is_verify','invitee_is_verify',
                'user' => ['gender','nickname'],
                'inviteuser' => ['gender','nickname'],
            ]);
            $item->append([
                'meet_time_text', 'status_text',
                'user' => ['avatar_text'],
                'inviteuser' => ['avatar_text']
            ]);
        }

        return $list;
    }

}
