<?php

namespace app\common\model;

use app\common\library\Dict;
use app\common\library\pay\Payment;
use DI\CompiledContainer;
use fast\Tree;
use think\Model;
use think\Request;
use app\common\model\Invite;
use think\Config;

/**
 * 邀约召集表
 */
class InviteCall Extends Model
{

    protected $name = 'invite_call';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;

    protected $insert = [
        "status"  => Dict::INVITE_CALL_STATUS_UN
    ];

    protected $type = [
        "package" => 'json',
    ];

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id')->setEagerlyType(0);
    }

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function shop()
    {
        return $this->belongsTo('app\common\model\Shop', 'shop_id')->setEagerlyType(0);
    }

    public function getMeetTimeTextAttr($value, $row)
    {
        return $row['meet_time'] ? date("Y-m-d H:i", $row['meet_time']) : '';
    }

    public function getStatusTextAttr($value, $row)
    {
        return Dict::getInviteCallStatus($row['status']);
    }

   /**
     * 发起召集
     * @param User $user
     * @param $params
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function initiate(User $user, $params = [])
    {
        //餐费不能用余额支付
//        if($params['pay_mode'] != Dict::INVITE_PAY_MODE_YOU && $params['pay_type'] == Dict::PAY_TYPE_BALANCE) {
//            throw new \Exception("不能使用余额支付");
//        }

        //门店
        $shopId = $params['shop_id'] ?? null;
        if(empty($shopId)) {
            throw new \Exception("请选择门店");
        }
        $shop = Shop::get($shopId);
        if(!$shop) {
            throw new \Exception("门店不存在");
        }

        list($packageId,$package,$meetingRedEnvelopePrice) = (new Invite())->checkShop($shop,$params);

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
            throw new \Exception("您当天已有待确认或待见面的邀约");
        }

        //只允许选择第2天 - 第14天
        $secondDay   = strtotime('+1 day', strtotime(date("Y-m-d")));
        $fourteenDay = strtotime('+14 days', strtotime(date("Y-m-d")));
        if($meetTime > $fourteenDay || $meetTime < $secondDay) {
            throw new \Exception("见面时间只能选择第2天-第14天");
        }

        //当天10点
        $_tmpTen = strtotime(date("Y-m-d 10:00", $meetTime));
        //当天12点
        $_tmpTwelve = strtotime(date("Y-m-d 21:00", $meetTime));

        // 10：00 - 21：00
        if($meetTime > $_tmpTwelve || $meetTime < $_tmpTen) {
            throw new \Exception("见面时间为每天10：00-21：00");
        }

        try {
            //支付
            $result = $this->pay($user, array_merge($params, [
                'meet_time'  => $meetTime,
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
     * 支付
     * @param User $user
     * @param $params
     * @return InviteLog
     * @throws \Exception
     */
    public function pay(User $user, $params)
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
                "publish_fee"  => config('site.invite_fee') ?: 0,
                'meeting_red_envelope_price' => $params['meeting_red_envelope_price'],
                "deposit" => $deposit,
                "deposit_pay_type" => $params['pay_mode'] == Dict::INVITE_PAY_MODE_YOU ? $params['pay_type'] : null,
            ]), true);

            if($result === false) {
                throw new \Exception("邀约失败");
            }

            //付款
            $payment = new Payment($user, $params['pay_type']);
            $result = $payment->call($result);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

        return $result;
    }

    /**
     * 召集大厅列表
     * @param User $user
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function hall(User $user)
    {
        $list = $this->with(['user' => ['cert'], 'shop'])->where([
            "invite_call.create_time" => ['>', strtotime('-7 days')],
            "invite_call.status"      => Dict::INVITE_CALL_STATUS_PROCESS,
        ])->order('invite_call.create_time', 'desc')
        ->paginate(20);

        //我感兴趣的召集
        $concern = model("app\common\model\InviteCallConcern")
            ->where('user_id', $user->id)
            ->whereIn('invite_call_id', array_column($list->items(), 'id'))
            ->column('invite_call_id');

        $areaList = model('app\common\model\AreaNew')->column('id,name');

        foreach($list as $key => $item)
        {
            $item->user->birth_year = date('y', strtotime($item->user->birth));
            $item->user->area = DMUserArea($areaList, $item->user->area_path);

            if($item->pay_mode == Dict::INVITE_PAY_MODE_MY) {
                $item->pay_mode = Dict::INVITE_PAY_MODE_YOU;
            }

            if($item->pay_mode == Dict::INVITE_PAY_MODE_YOU) {
                $item->pay_mode = Dict::INVITE_PAY_MODE_MY;
            }

            $item->is_concern = in_array($item->id, $concern) ? Dict::IS_TRUE : Dict::IS_FALSE;

            //门店区域
            $areaPath = $item->shop->area_path ?: "";
            $areaPath = explode(",", trim($areaPath, ','));
            $areaLastId = array_pop($areaPath);
            $item->shop->area_name = $areaList[$areaLastId] ?? "";

            $item->visible([
                'id', 'address', 'pay_mode', 'price', 'package', 'shop_id','shop_type','meeting_red_envelope_price',
                'user' => ['id','nickname','gender','height', 'area','is_member'],
                'shop' => ['id', 'name', 'area_name']
            ]);
            $item->append([
                'meet_time_text', 'is_concern',
                'user' => ['avatar_text', 'birth_year', 'avatar_text', 'is_cert_education', 'education_type_text', 'is_cert_realname', 'is_cert_work', 'work_type_text']
            ]);
        }

        return $list;
    }

    /**
     * 我发起的列表
     * @param User $user
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function mine(User $user)
    {
        $list = $this->with(['user' => ['cert'], 'shop'])->where([
                'invite_call.user_id' => $user->id,
                'invite_call.status'  => ["<>", Dict::INVITE_CALL_STATUS_UN]
            ])->order('invite_call.create_time', 'desc')
            ->paginate(20);

        $areaList = model('app\common\model\AreaNew')->column('id,name');

        foreach($list as $key => $item)
        {
            $item->invite_count = 0;
            $item->invite_name = "";
            $item->invite_avatars = [];

            $item->user->birth_year = date('y', strtotime($item->user->birth));
            $item->user->area = DMUserArea($areaList, $item->user->area_path);

            if($item->status ==  Dict::INVITE_CALL_STATUS_PROCESS) {//进行中
                $concert = model('app\common\model\InviteCallConcern')->with(['user'])
                    ->where([
                        "invite_call_concern.invite_call_id"  => $item->id
                    ])->select();
                $item->invite_count = count($concert);
                $avatars = [];
                foreach($concert as $_ckey => $_citem) {
                    array_push($avatars, cdnurl($_citem->user->avatar, true));
                }
                $item->invite_avatars = $avatars;

            }

            if($item->status == Dict::INVITE_CALL_STATUS_SUCCESS) {//成功
                //查找邀约记录
                $invite = model('app\common\model\Invite')->get($item->invite_id, ['inviteuser']);
                if($invite) {
                    $item->invite_name = $invite->inviteuser->nickname;
                    $item->invite_avatars = [cdnurl($invite->inviteuser->avatar, true)];
                }
            }

            //门店区域
            $areaPath = $item->shop->area_path ?: "";
            $areaPath = explode(",", trim($areaPath, ','));
            $areaLastId = array_pop($areaPath);
            $item->shop->area_name = $areaList[$areaLastId] ?? "";
            $item->visible([
                'id', 'address', 'pay_mode', 'price', 'package', 'status', 'invite_id', 'invite_count', 'invite_name', 'invite_avatars','deposit','shop_type','meeting_red_envelope_price',
                'user' => ['id','nickname','gender','height', 'area','is_member'],
                'shop' => ['id', 'name', 'area_name']

            ]);
            $item->append([
                'meet_time_text', 'status_text',
                'user' => ['avatar_text', 'birth_year', 'avatar_text', 'is_cert_education', 'education_type_text', 'is_cert_realname', 'is_cert_work', 'work_type_text']
            ]);
        }

        return $list;
    }

}
