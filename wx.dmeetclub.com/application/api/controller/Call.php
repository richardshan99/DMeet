<?php

namespace app\api\controller;

use app\common\library\Dict;
use app\common\model\InviteCall;
use app\common\model\InviteCallConcern;
use think\Request;

/**
 * 见面接口
 */
class Call extends BaseApi
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $user;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        /** @var \app\common\model\User user */
        $this->user = $this->getUser();
    }

    /**
     * 发起召集
     * @param Request $request
     */
   public function initiate(Request $request)
   {
       $data = $request->post();
       $result = $this->validate($data, 'Call.initiate');

       if($result !== true) {
           $this->renderError($result);
       }

       try {
           $result = (new \app\common\model\InviteCall)->initiate($this->user, $data);
       }catch (\Exception $ex) {
           $this->renderError($ex->getMessage());
       }

       $this->renderSuccess($result);
   }

    /**
     * 召集大厅
     */
   public function hall()
   {
       $this->renderSuccess((new InviteCall)->hall($this->user));
   }

    /**
     * 感兴趣列表
     */
    public function concernHall()
    {
        $this->renderSuccess((new InviteCallConcern)->hall($this->user));
    }

    /**
     * 我发起的
     */
    public function mine()
    {
        //角标
        $badge = (new InviteCallConcern)->badge($this->user);
        $list = (new InviteCall)->mine($this->user);
        $this->renderSuccess(compact('badge', 'list'));
    }

    /**
     * 感兴趣/取消兴趣
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function concern(Request $request)
    {
        $callId = $request->post('call_id', null, 'trim,intval');
        $call = model('app\common\model\InviteCall')->where([
            "id"     => $callId,
            "status" => Dict::INVITE_CALL_STATUS_PROCESS,
        ])->find();
        if(!$call) {
            $this->renderError("不可操作");
        }

        if($call->user_id == $this->user->id) {
            $this->renderError("不能操作自己发布的召集信息");
        }

        $concert = model('app\common\model\InviteCallConcern')->where([
            "invite_call_id"  => $callId,
            "user_id"         => $this->user->id,
        ])->find();
        if($concert) { //存在则删除
            $result = $concert->delete();
        } else { //不存在则创建
            $result = InviteCallConcern::create([
                "invite_call_id"  => $callId,
                "user_id"         => $this->user->id,
            ], true);
        }

        if($result !== false) {
            $this->renderSuccess([], '操作成功');
        }
        $this->renderError('操作失败');
    }

    /**
     * 我发起的感兴趣的用户列表
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function concernList(Request $request)
    {
        $callId = $request->post('call_id', null, 'trim,intval');
        $call = model('app\common\model\InviteCall')->where([
            "id"      => $callId,
            "status"  => Dict::INVITE_CALL_STATUS_PROCESS,
            "user_id" => $this->user->id
        ])->find();
        if(!$call) {
            $this->renderError("不可查看");
        }

        $list = (new InviteCallConcern)->showUser($call);
        $this->renderSuccess($list);
    }

    /**
     * 选中邀约
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function invitation(Request $request)
    {
        $callId = $request->post('call_id', null, 'trim,intval');
        $inviteeId = $request->post('user_id', null, 'trim,intval');
        $call = model('app\common\model\InviteCall')->where([
            "id"      => $callId,
            "status"  => Dict::INVITE_CALL_STATUS_PROCESS,
            "user_id" => $this->user->id
        ])->find();
        if(!$call) {
            $this->renderError("不可查看");
        }

        //查询选中用户是否点了兴趣
        $isExists = model('app\common\model\InviteCallConcern')
            ->where([
                "invite_call_id" => $call->id,
                "user_id"        => $inviteeId
            ])->find();
        if(!$isExists) {
            $this->renderError("您选中的用户对此次召集不感兴趣");
        }

        try{
            $ret = (new \app\common\model\Invite)->call($this->user, $inviteeId, $call);
        }catch (\Exception $ex) {
            $this->renderError($ex->getMessage());
        }

        if($ret !== false) {
            $this->renderSuccess([], "邀约成功");
        }
        $this->renderSuccess("邀约失败");
    }
}
