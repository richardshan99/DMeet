<?php

namespace app\api\controller;

use app\common\library\Dict;
use app\common\model\Chat;
use app\common\model\InviteCall;
use app\common\model\InviteCallConcern;
use app\common\model\MessageSystem;
use app\common\model\UserMember;
use app\common\model\UserMessage;
use think\Db;
use think\Request;

/**
 * 我的 - 个人名片  - 消息
 */
class Message extends BaseApi
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
     * 消息模版
     */
    public function template()
    {
        $list = model('app\common\model\Message')->field('id,content')->select();
        $this->renderSuccess($list);
    }

    /**
     * 消息列表
     */
    public function list()
    {
        $list = [
            ["key" => 'chat', "name" => "打招呼", 'content' => "", 'time' => "", "count" => 0],
            ["key" => 'invitation', "name" => "邀约", 'content' => "", 'time' => "", "count" => 0],
            ["key" => 'like', "name" => "点赞", 'content' => "", 'time' => "", "count" => 0],
            ["key" => 'system', "name" => "系统消息", 'content' => "", 'time' => "", "count" => 0],
        ];

        foreach($list as $key => $item) {
            $content = $time = "";
            $count = 0;
            if($item['key'] == 'chat') { //打招呼
                $lastChat = model('app\common\model\Chat')->with(['fromuser'])
                    ->where('to_user_id', $this->user->id)
                    ->order('create_time', 'desc')
                    ->find();
                if($lastChat) {
                    $content = $lastChat->fromuser->nickname .": ". $lastChat->message;
                    $time    = date("Y-m-d H:i", $lastChat->create_time);
                }

                $count = (new UserMessage)->calChat($this->user);
            }

            if($item['key'] == 'invitation') { //邀约
                $lastInvitation = model('app\common\model\MessageInvitation')->with(['fromuser'])
                    ->where('user_id', $this->user->id)
                    ->order('create_time', 'desc')
                    ->find();
                if($lastInvitation) {
                    $content = $lastInvitation->fromuser->nickname .($lastInvitation->biz_type == 1 ? " 对您发起见面邀约" : " 接受了您的见面邀约");
                    $time    = date("Y-m-d H:i", $lastInvitation->create_time);
                }

                $count = (new UserMessage)->calInvitation($this->user);
            }

            if($item['key'] == 'like') { //点赞
                $lastLike = model('app\common\model\MessageLikes')->with(['likeuser'])
                    ->where('user_id', $this->user->id)
                    ->order('create_time', 'desc')
                    ->find();
                if($lastLike) {
                    $content = $lastLike->likeuser->nickname . ' 点赞了您的动态';
                    $time    = date("Y-m-d H:i", $lastLike->create_time);
                }

                $count = (new UserMessage)->calLikes($this->user);

            }

            if($item['key'] == 'system') { //系统消息
                $lastSystem = model('app\common\model\MessageSystem')
                    ->where('user_id', $this->user->id)
                    ->order('create_time', 'desc')
                    ->find();
                if($lastSystem) {
                    $content = $lastSystem->message;
                    $time    = date("Y-m-d H:i", $lastSystem->create_time);
                }

                $count = (new UserMessage)->calSystem($this->user);
            }
            $list[$key]['content'] = $content  ?? "";
            $list[$key]['time']    = $time  ?? "";
            $list[$key]['count']   = $count  ?? 0;

        }

        $this->renderSuccess($list);
    }

    /**
     * 点赞列表
     */
    public function likesList()
    {
        //更新点赞最后更新时间
        (new UserMessage)->flushLikesTime($this->user);

        $list = model('app\common\model\MessageLikes')->with(['likeuser'])
            ->where('user_id', $this->user->id)
            ->order('create_time', 'desc')
            ->paginate(20);

        foreach($list as $key => $item)
        {
            $item->visible(['like_blog_id','likeuser' => ['nickname']]);
            $item->append(['image_text', 'likeuser' => ['avatar_text'], 'create_time_text']);
        }
        $this->renderSuccess($list);
    }

    /**
     * 邀约列表
     */
    public function invitationList()
    {
        //更新邀约最后更新时间
        (new UserMessage)->flushInvitationTime($this->user);

        $list = model('app\common\model\MessageInvitation')->with(['fromuser'=>['cert']])
            ->where('user_id', $this->user->id)
            ->order('create_time', 'desc')
            ->paginate(20);
        $areaNew = model('app\common\model\AreaNew')->column('id,name');
        foreach($list as $key => $item)
        {
            $areaPath = $item->fromuser->area_path;
            $item->fromuser->area = DMUserArea($areaNew, $areaPath);
            $item->visible([
                'fromuser' => [
                    'nickname', 'height', 'is_cert_realname', 'is_cert_work', 'is_cert_education', 'gender', 'is_member',
                    'school'
                ]
            ]);
            $item->append(['fromuser' => ['area','avatar_text', 'birth_text', 'work_type_text', 'education_type_text'], 'create_time_text', 'biz_type']);
        }
        $this->renderSuccess($list);
    }

    /**
     * 消息留言
     * @param Request $request
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function send(Request $request)
    {
        $toUserId = $request->post('to_user_id', null, 'intval,trim');
        $messageId = $request->post('message_id', null, 'intval,trim');
        if(!$toUserId || !$messageId) {
            $this->renderError("发送失败");
        }

        $toUser = model('app\common\model\User')->get($toUserId);
        if(!$toUser) {
            $this->renderError("用户不存在，发送失败");
        }
        //判断消息模板是否存在
        $message = model('app\common\model\Message')->get($messageId);
        if(!$message) {
            $this->renderError("模板不存在，发送失败");
        }

        if($this->user->id == $toUserId) {
            $this->renderError("不能给自己发消息");
        }
        
          //发送模板消息 @time 2024-9-27
        try {
            if($toUser['email']){
                $subject = "留言通知";
                $body = "尊敬的#".$toUser['nickname']."，<br>您收到了#".$this->user->nickname."给您发的一条消息，<br>请登录DMeet直面 微信小程序查看。".Dict::EMAIL_TEXT;
                (new \app\common\library\NewEmail)->send($toUser['email'],$subject,$body);                       
                // 邮件同时发送给自己，by Richard  
                (new \app\common\library\NewEmail)->send('richard@dmeetclub.com', $subject, $body);                  
                } 
        } catch (\Exception $ex) {} 
        
        $result = (new \app\common\model\Chat)->send($this->user->id, $toUser->id, $message);
        if($result !== false) {
            $this->renderSuccess([], "发送成功");
        }

    }

    /**
     * 对话列表
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function chatList(Request $request)
    {
        $userId = $request->post('user_id', null, 'trim,intval');

        $list = (new Chat)->getList($this->user->id, $userId);
        $this->renderSuccess($list);
    }

    /**
     * @param Request $request
     * @throws \think\exception\DbException
     */
    public function chatUserList(Request $request)
    {
        $list = model('app\common\model\ChatRelation')->with(['relationuser'])
            ->where([
                'chat_relation.user_id' => $this->user->id
            ])
            ->order('update_time', 'desc')->paginate(20);
//        echo model('app\common\model\Chat')->table($dialTable.' dt')->with(['fromuser'])->getLastSql();exit;
        foreach($list as $key => $item)
        {
            $item->message = model('app\common\model\Chat')->whereRaw(
                    "from_user_id = {$item->user_id} and to_user_id = {$item->relation_user_id} or 
                     from_user_id = {$item->relation_user_id} and to_user_id = {$item->user_id}"
                )->order('create_time', 'desc')->value('message');
            $item->visible(['relationuser' => ['id', 'nickname'], 'message', 'unread_num']);
            $item->append(['relationuser' => ['avatar_text'], 'update_time_text']);
        }
        $this->renderSuccess($list);
    }

    /**
     * 消息 - 系统消息列表
     */
    public function systemList()
    {
        $list = (new MessageSystem)->getList($this->user);
        $this->renderSuccess($list);
    }
}
