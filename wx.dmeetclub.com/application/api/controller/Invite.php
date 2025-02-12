<?php

namespace app\api\controller;

use app\common\model\BlogReport;
use app\common\library\Dict;
use DI\CompiledContainer;
use app\common\model\InviteChange;
use app\common\model\Shop;
use think\Request;

/**
 * 邀约接口
 */
class Invite extends BaseApi
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
     * 列表
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function todoList(Request $request)
    {
        $todayStart = strtotime(date('Y-m-d 00:00:00'));
        $todayEnd = strtotime(date('Y-m-d 23:59:59'));

        $whereRaw = "invite.status in 
            (".Dict::INVITE_STATUS_WAIT_CONFIRM.",".Dict::INVITE_STATUS_WAIT_MEET.")
            or (invite.status = ".Dict::INVITE_STATUS_FINISH." AND invite.meet_time between 
            ".$todayStart." AND ".$todayEnd.")";
            
        // $list = (new \app\common\model\Invite)->list($this->user, ["invite.status" => ["in", [Dict::INVITE_STATUS_WAIT_CONFIRM, Dict::INVITE_STATUS_WAIT_MEET]]],$this->timezone);
        $list = (new \app\common\model\Invite)->list($this->user, [],$this->timezone,$whereRaw);
        $this->renderSuccess($list);
    }

    /**
     * 查看历史邀约 - 我的邀约
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function myInvitationList(Request $request)
    {
        $status = $request->post('status', null, 'trim,intval');
        $list = (new \app\common\model\Invite)->list($this->user, [
            // "invite.user_id" => $this->user->id,
            "invite.status" => ['in', $status ? [$status] : [
                Dict::INVITE_STATUS_WAIT_CONFIRM,
                Dict::INVITE_STATUS_WAIT_MEET,
                Dict::INVITE_STATUS_FINISH,
                Dict::INVITE_STATUS_CANCEL,
            ]]
        ],$this->timezone);
        $this->renderSuccess($list);
    }

    /**
     * 查看历史邀约 - 邀约我的
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function inviteMeList(Request $request)
    {
        $status = $request->post('status', null, 'trim,intval');
        $list = (new \app\common\model\Invite)->list($this->user, [
            "invite.invite_user_id" => $this->user->id,
            "invite.status" => ['in', $status ? [$status] : [
                Dict::INVITE_STATUS_WAIT_CONFIRM,
                Dict::INVITE_STATUS_WAIT_MEET,
                Dict::INVITE_STATUS_FINISH,
                Dict::INVITE_STATUS_CANCEL,
            ]]
        ],$this->timezone);
        $this->renderSuccess($list);
    }

    /**
     * 我的邀约 / 邀约我的 - 角标
     * @param Request $request
     */
    public function badge(Request $request)
    {
        //我的邀约
        $myInvitation = model('app\common\model\Invite')->where([
            "user_id" => $this->user->id,
            "status" => ['in', [
                Dict::INVITE_STATUS_WAIT_CONFIRM,
                Dict::INVITE_STATUS_WAIT_MEET,
//                Dict::INVITE_STATUS_FINISH,
//                Dict::INVITE_STATUS_CANCEL,
            ]]
        ])->group('status')->column('status, count(id) as _count');
        $list['my_invitation'] = [
            "wait_confirm" => $myInvitation[Dict::INVITE_STATUS_WAIT_CONFIRM] ?? 0,
            "wait_meet" => $myInvitation[Dict::INVITE_STATUS_WAIT_MEET] ?? 0,
//                "finish" => 0,
//                "wait_confirm" => 0,
        ];
            //我的邀约
        $invitationMe = model('app\common\model\Invite')->where([
            "invite_user_id" => $this->user->id,
            "status" => ['in', [
                Dict::INVITE_STATUS_WAIT_CONFIRM,
                Dict::INVITE_STATUS_WAIT_MEET,
//                Dict::INVITE_STATUS_FINISH,
//                Dict::INVITE_STATUS_CANCEL,
            ]]
        ])->group('status')->column('status, count(id) as _count');
        $list["invite_me"] = [
            "wait_confirm" => $invitationMe[Dict::INVITE_STATUS_WAIT_CONFIRM] ?? 0,
            "wait_meet" => $invitationMe[Dict::INVITE_STATUS_WAIT_MEET] ?? 0,
//                "finish" => 0,
//                "wait_confirm" => 0,
        ];

        $this->renderSuccess($list);
    }

    /**
     * 我邀约的/待确认 - 撤回邀约
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function revoke(Request $request)
    {
        $invite = \app\common\model\Invite::get([
            "id" => $request->post('invite_id', null, 'trim,intval'),
            "status" => Dict::INVITE_STATUS_WAIT_CONFIRM,
            "user_id" => $this->user->id,
        ]);

        if (!$invite) {
            $this->renderError("当前邀约无法撤回");
        }

        try {
            $library = new \app\common\library\Invite($this->user, $invite);
            $result = $library->revoke();
        } catch (\Exception $ex) {
            $this->renderError($ex->getMessage());
        }

        if ($result !== false) {
            $this->renderSuccess([], "邀约撤销成功");
        }
        $this->renderError("邀约撤销失败");

    }

    /**
     * 邀约我的/待确认 - 同意
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function approve(Request $request)
    {
        $inviteId = $request->post('invite_id', null, 'trim,intval');
        $payType = $request->post('pay_type', null, 'trim,intval');

        $invite = \app\common\model\Invite::get([
            "id" => $inviteId,
            "status" => Dict::INVITE_STATUS_WAIT_CONFIRM,
            "invite_user_id" => $this->user->id,
        ]);

        if (!$invite) {
            $this->renderError("当前邀约无法操作");
        }

        if($invite->meet_time < time()) {
            $this->renderError("见面时间已过，无法操作");
        }

        //判断见面当天是否有待确认和待见面的订单
        $isExists = model('app\common\model\Invite')->where([
            "status" => Dict::INVITE_STATUS_WAIT_MEET,
            "user_id|invite_user_id" => $this->user->id,
            "meet_time" => ['between', [strtotime(date("Y-m-d", $invite->meet_time)), strtotime(date("Y-m-d 23:59:59", $invite->meet_time))]]
        ])->find();
        if($isExists) {
            $this->renderError("您当天已有待见面的邀约，无法同意");
        }

        try {
            $library = new \app\common\library\Invite($this->user, $invite);
            $result = $library->approve($payType);
        } catch (\Exception $ex) {
            $this->renderError($ex->getMessage());
        }

        if ($result !== false) {
            $this->renderSuccess($result);
        }
        $this->renderError("邀约撤销失败");
    }

    /**
     * 邀约我的/待确认 - 拒绝
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function reject(Request $request)
    {
        $inviteId = $request->post('invite_id', null, 'trim,intval');

        $invite = \app\common\model\Invite::get([
            "id" => $inviteId,
            "status" => Dict::INVITE_STATUS_WAIT_CONFIRM,
            "invite_user_id" => $this->user->id,
        ]);

        if (!$invite) {
            $this->renderError("您无法操作该邀约");
        }

        try {
            $library = new \app\common\library\Invite($this->user, $invite);
            $result = $library->reject();
        } catch (\Exception $ex) {
            $this->renderError($ex->getMessage());
        }

        if ($result !== false) {
            $this->renderSuccess([], '操作成功');
        }
        $this->renderError("操作失败");
    }

    /**
     * 待见面 - 取消
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function cancel(Request $request)
    {
        $inviteId = $request->post('invite_id', null, 'trim,intval');

        $invite = \app\common\model\Invite::get([
            "id" => $inviteId,
            "status" => Dict::INVITE_STATUS_WAIT_MEET,
            "user_id|invite_user_id" => $this->user->id,
        ]);

        if (!$invite) {
            $this->renderError("该邀约无法操作");
        }

        try {
            $library = new \app\common\library\Invite($this->user, $invite);
            $result = $library->cancel();
        } catch (\Exception $ex) {
            $this->renderError($ex->getMessage());
        }

        if ($result !== false) {
            $this->renderSuccess([], '操作成功');
        }
        $this->renderError("操作失败");
    }

    /**
     * 已完成 - 分享动态
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function share(Request $request)
    {
        $data = $request->post(null, null, "htmlspecialchars");
        $result = $this->validate($data, 'Blog.share');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->renderError($result);
        }

        //确认当前邀约已完成
        $invite = model('app\common\model\Invite')
            ->where([
                "status" => Dict::INVITE_STATUS_FINISH,
                "user_id|invite_user_id" => $this->user->id,
                "id"     => $data['invite_id']
            ])->find();
        if(!$invite) {
            $this->renderError("邀约无法分享动态");
        }

        //查看是否分享过
        $isExists = model('app\common\model\Blog')->where([
            "user_id" => $this->user->id,
            "style"   => Dict::BLOG_STYLE_INVITATION_MEET,
            "invite_id" => $invite->id
        ])->find();
        if($isExists) {
            $this->renderError("您已提交过分享");
        }
        $ret = \app\common\model\Blog::create(array_merge($data, [
            "user_id" => $this->user->id,
            "style"   => Dict::BLOG_STYLE_INVITATION_MEET,
            "invite_id" => $invite->id
        ]),true);

        if($ret !== false) {
            $this->renderSuccess([], "发布成功，请等待审核");
        }

        $this->renderError("发布失败");
    }

    /**
     * 核销码
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function qrcode(Request $request)
    {
        $inviteId = $request->post('invite_id', null, 'trim,intval');
        $invite = model('app\common\model\Invite')
            ->where([
                "id" => $inviteId,
                "shop_type" => Dict::SHOP_TYPE_RESTAURANT, // 餐厅才有核销码
                "user_id|invite_user_id" => $this->user->id
            ])->find();
        if(!$invite) {
            $this->renderError("邀约不存在");
        }

        if($invite->status != Dict::INVITE_STATUS_WAIT_MEET) { //状态不是待见面
            $this->renderError("邀约状态不是待见面，无法核销");
        }

        $text = [
            "invite_id" => $invite->id,
            "role"      => $this->user->id == $invite->user_id ? Dict::INVITATION_USER_TYPE_INVITER : Dict::INVITATION_USER_TYPE_INVITEE,
            "user_id"   => $this->user->id,
            "time"      => time()
        ];
        
        $text = http_build_query($text);
        $text .= "&sign=".md5($text);
        $qrcode = DMQrcode($text)->getDataUri();
        $this->renderSuccess(compact('qrcode'));
    }
    
     /**
     * 修改见面信息
     * @param Request $request
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function inviteChange(Request $request): void
    {
        $params = $request->post();
        $inviteId = $params['invite_id'] ?? null;
        $invite = model('app\common\model\Invite')
            ->where([
                "shop_type" => Dict::SHOP_TYPE_NO_RESTAURANT,
                "id" => $inviteId,
                "invite_user_id" => $this->user->id,
                "status" => Dict::INVITE_STATUS_WAIT_CONFIRM,
            ])->find();
        if(!$invite) {
            $this->renderError("未找到邀约信息");
        }

        $inviteChangeInfo = (new InviteChange)->where(['user_id'=>$this->user->id,'invite_id'=>$inviteId])->find();
        if(!empty($inviteChangeInfo)){
            $this->renderError("您已修改过见面信息");
        }

        //门店
        $shopId = $params['shop_id'] ?? null;

        if(empty($shopId)) {
            $this->renderError("请选择门店");
        }
        $shop = Shop::get($shopId);
        if(!$shop) {
            $this->renderError("门店不存在");
        }
        if($shop->type != Dict::SHOP_TYPE_NO_RESTAURANT){
            $this->renderError("门店类型不正确");
        }

        //见面时间
        $meetTime = $params['meet_time'] ?? null;
        if(empty($meetTime)) {
            $this->renderError("请选择见面时间");
        }
        $meetTime = strtotime($meetTime);

        //验证对方每天只允许有一次待见面 & 待确认的邀约
        $isExists = model('app\common\model\Invite')->where([
            'id' => ['<>',$inviteId],
            'user_id|invite_user_id' => $invite['user_id'],
            'status'  => ['in', [Dict::INVITE_STATUS_WAIT_MEET, Dict::INVITE_STATUS_WAIT_CONFIRM]],
            'meet_time' => ['between', [strtotime(date("Y-m-d", $meetTime)), strtotime(date("Y-m-d 23:59:59", $meetTime))]]
        ])->find();

        if($isExists) {
            $this->renderError("该时间段对方已被其他邀约占用");
            throw new \Exception("每天只允许有一次待确认或待见面邀约");
        }

        $res = (new InviteChange)->allowField(true)->save(
            [
                'user_id' => $this->user->id,
                'invite_id' => $inviteId,
                'meet_time' =>  timezongTimestamp($this->timezone,$meetTime),
                'shop_id' => $shop->id,
                'address' => $shop->address,
            ]
        );
        if(!$res){
            $this->renderError("提交失败");
        }
        $this->renderSuccess([],'提交成功');
    }

    /**
     * 处理见面信息修改
     * @param Request $request
     * @return void
     */
    public function handleChange(Request $request): void
    {
        $inviteId = $request->post('invite_id', 0, 'trim,intval');
        $scene = $request->post('scene', 'agree', 'trim');
        list($status,$msg) = (new InviteChange)->handleChange($inviteId,$scene,$this->user);
        if(!$status){
            $this->renderError($msg);
        }
        $this->renderSuccess([],'操作成功');
    }

    /**
     * 签到
     * @param Request $request
     * @return void
     * @throws \think\exception\DbException
     */
    public function signIn(Request $request)
    {
        $data = $request->post();

        $invite = \app\common\model\Invite::get([
            "id" => $data['invite_id'],
            "shop_type" => Dict::SHOP_TYPE_NO_RESTAURANT,
            "status" => Dict::INVITE_STATUS_WAIT_MEET,
        ]);

        if(!$invite) {
            $this->renderError("邀约不存在");
        }

        if($invite->user_id == $this->user->id && $invite['inviter_is_verify'] == Dict::IS_TRUE){
            $this->renderError("您已签到");
        }
        
        if($invite->invite_user_id == $this->user->id && $invite['invitee_is_verify'] == Dict::IS_TRUE){
            $this->renderError("您已签到");
        }
        

        try {
            $library = new \app\common\library\Invite($this->user, $invite);
            $result = $library->signIn($data,$this->timezone);
        } catch (\Exception $ex) {
            $this->renderError($ex->getMessage());
        }

        if ($result !== false) {
            $this->renderSuccess([], '操作成功');
        }
        $this->renderError("操作失败");
    }

}
