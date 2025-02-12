<?php

namespace app\common\model;

use app\common\library\Dict;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Model;

/**
 * 感兴趣的
 */
class InviteCallConcern Extends Model
{

    protected $name = 'invite_call_concern';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    protected $insert = [

    ];

    /**
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id')->setEagerlyType(0);
    }

    public function call()
    {
        return $this->belongsTo('app\common\model\InviteCall', 'invite_call_id')->setEagerlyType(0);
    }

    /**
     * 召集大厅列表 -- 感兴趣
     * @param User $user
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function hall(User $user)
    {
        $list = $this->with(['call' => ['shop', 'user']])
            ->join('__INVITE_CALL__ ic', 'invite_call_concern.invite_call_id = ic.id')
            ->where([
                "ic.status"      => Dict::INVITE_CALL_STATUS_PROCESS,
                "invite_call_concern.user_id"     => $user->id
            ])->order('ic.create_time', 'desc')
            ->paginate(20);
        $areaList = model('app\common\model\AreaNew')->column('id,name');
        foreach($list as $key => $item)
        {
            $item->user = $item->call->user;
            $item->user->birth_year = date('y', strtotime($item->user->birth));
            $item->user->area = DMUserArea($areaList, $item->user->area_path);

            $item->pay_mode = $item->call->pay_mode;
            if($item->call->pay_mode == Dict::INVITE_PAY_MODE_MY) {
                $item->pay_mode = Dict::INVITE_PAY_MODE_YOU;
            }

            if($item->call->pay_mode == Dict::INVITE_PAY_MODE_YOU) {
                $item->pay_mode = Dict::INVITE_PAY_MODE_MY;
            }

            $item->is_concern =  Dict::IS_TRUE;
            $call = $item->call;
            $item->id = $call->id;
            $item->meet_time_text =  $call->meet_time_text;
            $item->address = $call->address;
            $item->price = $call->price;
            $item->package = $call->package;

            $item->shop = $item->call->shop;

            //门店区域
            $areaPath = $item->shop->area_path ?: "";
            $areaPath = explode(",", trim($areaPath, ','));
            $areaLastId = array_pop($areaPath);
            $item->shop->area_name = $areaList[$areaLastId] ?? "";

            $item->visible([
                'id', 'address', 'pay_mode', 'price', 'package','shop_type','meeting_red_envelope_price',// 餐厅类型
                'user' => ['id','nickname','gender','height','is_member', 'school'],
                'shop' => ['id', 'name', 'area_name']
            ]);
            $item->append([
                'meet_time_text', 'is_concern',
                'user' => ['avatar_text', 'area', 'birth_year', 'avatar_text', 'is_cert_education', 'education_type_text', 'is_cert_realname', 'is_cert_work', 'work_type_text']
            ]);
        }

        return $list;
    }

    /**
     * 计算我发起进行中的召集感兴趣人数
     * @param User $user
     * @return int|string
     * @throws \think\Exception
     */
    public function badge(User $user)
    {
        return $this->alias('invite_call_concern')->join('__INVITE_CALL__ ic', 'invite_call_concern.invite_call_id = ic.id')
            ->where([
                "ic.create_time" => ['>', strtotime('-7 days')],
                "ic.status"      => Dict::INVITE_CALL_STATUS_PROCESS,
                "ic.user_id"     => $user->id
            ])->count();
    }

    /**
     * 查看感兴趣的用户
     * @param InviteCall $inviteCall
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function showUser(InviteCall $inviteCall)
    {
        $list = $this->with(['user' => ['cert']])
            ->where([
                "invite_call_concern.invite_call_id"  => $inviteCall->id
            ])->paginate(20);

        $areaList = model('app\common\model\AreaNew')->column('id,name');

        foreach($list as $key => $item)
        {
            $item->user->birth_year = date('y', strtotime($item->user->birth));
            $item->user->area = DMUserArea($areaList, $item->user->area_path);
            $item->call_id = $item->invite_call_id;
            $item->visible([
                "call_id",
                'user' => ['id','nickname','gender','height', 'area','is_member', 'school']
            ]);
            $item->append([
                'user' => ['avatar_text', 'birth_year', 'avatar_text', 'is_cert_education', 'education_type_text', 'work_type_text']
            ]);
        }

        return $list;
    }
}
